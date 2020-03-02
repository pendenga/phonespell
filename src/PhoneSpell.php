<?php

namespace Pendenga\PhoneSpell;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function PHPUnit\Framework\isNull;

/**
 * Class PhoneSpell
 * @package Pendenga\PhoneSpell
 */
class PhoneSpell
{
    use LoggerAwareTrait;

    const PHONE       = [
        0 => [0],
        1 => [1],
        2 => [2, 'a', 'b', 'c'],
        3 => [3, 'd', 'e', 'f'],
        4 => [4, 'g', 'h', 'i'],
        5 => [5, 'j', 'k', 'l'],
        6 => [6, 'm', 'n', 'o'],
        7 => [7, 'p', 'q', 'r', 's'],
        8 => [8, 't', 'u', 'v'],
        9 => [9, 'w', 'x', 'y', 'z'],
    ];
    const LETTER_REPL = [
        0   => ['O'],
        1   => ['L', 'I'],
        3   => ['E'],
        4   => ['A'],
        5   => ['S'],
        7   => ['T'],
        8   => ['B'],
        9   => ['g'],
        //'a' => [4],
        //'b' => [8],
        'c' => ['K', 'X'],
        //'e' => [3],
        //'g' => [9],
        'k' => ['X'], // c
        //'o' => [0],
        //'s' => ['$'],
        //'t' => [7],
        'x' => ['K'], // c
    ];
    const WORD_REPL   = [
        'u'  => ['00', 'oo'],
        'oo' => ['00', 'u'],
    ];

    /**
     * @var WordListFactory
     */
    protected $factory;

    /**
     * @var WordListFilter[]
     */
    protected $word_lists;

    /**
     * @var int
     */
    protected $word_length;
    protected $global_prefix = '';
    protected $raw_findings = [];

    /**
     * PhoneSpell constructor.
     * @param WordListFactory $factory
     * @param LoggerInterface $logger
     */
    public function __construct(WordListFactory $factory, LoggerInterface $logger = null)
    {
        $this->factory = $factory;
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * @param      $word
     * @param null $start
     * @return array
     */
    public function allPermutations($word, $start = null)
    {
        $this->word_length = strlen($word);
        $output = [];

        // $this->logger->debug(__METHOD__ . " with start: {$word} ({$start})");
        if (strlen($word) == 1) {
            foreach (self::letterOptions($word) as $letter) {
                $full_word = $start . $letter;
                $output[] = $full_word;
            }
        } else {
            $first = current(str_split($word));
            foreach (self::letterOptions($first) as $letter) {
                $rest = substr($word, 1, strlen($word));
                $partial_word = $start . $letter;
                $output = array_merge($output, self::allPermutations($rest, $partial_word));
            }
        }

        return $output;
    }

    /**
     * $findings is words from the dictionary.
     * This converts any letter replacements back into the characters found in the phone number.
     * @param string $phone_number
     * @param array  $findings
     * @return array
     */
    public function decodeFindingsRaw($phone_number, array $findings)
    {
        $original = str_split($phone_number);
        $output = [];
        foreach ($findings as $found) {
            try {
                $new_word = null;
                foreach (str_split($found) as $i => $letter) {
                    if ($letter == '*') {
                        $new_word .= '*';
                        continue;
                    }
                    if (in_array($letter, self::PHONE[$original[$i]])) {
                        $new_word .= $letter;
                        continue;
                    }
                    if (isset(self::LETTER_REPL[$original[$i]])) {
                        if (in_array($letter, self::LETTER_REPL[$original[$i]])) {
                            $new_word .= $original[$i];
                            continue;
                        } elseif (in_array(strtoupper($letter), self::LETTER_REPL[$original[$i]])) {
                            $new_word .= $original[$i];
                            continue;
                        }
                    }

                    if (isset(self::LETTER_REPL[$letter])) {
                        foreach (self::LETTER_REPL[$letter] as $repl) {
                            if (in_array(strtolower($repl), self::PHONE[$original[$i]])) {
                                $new_word .= $repl;
                                continue;
                            }
                        }
                    }
                    throw new PhoneSpellException('letter not decoded ' . $letter . ' in ' . $found);
                }

                $output[] = $new_word;
            } catch (PhoneSpellException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $output;
    }

    /**
     * From raw findings, combine words and order the whole list by score
     * @param array $findings
     * @return array
     */
    public function combineWordsAndOrder(array $findings)
    {
        $tmp_meta = [];
        $scored = [];

        // add temporary metadata and scores to each word
        foreach ($findings as $found) {
            $new_row = ['word' => $found];
            if (preg_match("/([*]*)([^*]*)([*]*)/", $found, $matches)) {
                $new_row['pre'] = $matches[1];
                $new_row['partial'] = $matches[2];
                $new_row['post'] = $matches[3];
            }
            $score = pow(2, strlen($new_row['partial'])) -
                     strlen($new_row['pre']) -
                     strlen($new_row['post']);
            $tmp_meta[] = $new_row;
            $scored[$score][] = $new_row['word'];
        }

        // try to combine words...
        foreach ($tmp_meta as $foo) {
            foreach ($tmp_meta as $bar) {
                if ($foo['word'] == $bar['word']) {
                    continue;
                }
                if (strlen($foo['post']) > strlen($bar['pre'])) {
                    continue;
                }
                if (strlen($foo['pre']) + strlen($foo['partial']) <= strlen($bar['pre'])) {
                    $new_word = str_pad($foo['pre'] . $foo['partial'], strlen($bar['pre']), '*') .
                                $bar['partial'] .
                                $bar['post'];

                    $score = pow(2, strlen($foo['partial'])) + pow(2, strlen($bar['partial'])) -
                             strlen($foo['pre']) -
                             strlen($bar['post']);
                    $scored[$score][] = $new_word;
                    continue;
                }
            }
        }
        krsort($scored);

        // flatten scored array of arrays
        $output = [];
        foreach ($scored as $score => $set) {
            $output = array_merge($output, $set);
        }

        return $output;
    }

    /**
     * This should do one final analysis on the words to determine the most desirable results
     * - Words with longer phrases of letters, with less numbers interspersed should be first.
     * - Words with more letters should be first.
     * @param array $words
     * @return array
     */
    public function finalOrdering(array $words) {
        // TODO: come up with sorting algorithm
        return $words;
    }

    /**
     * @param WordListFactory $factory
     * @param LoggerInterface $logger
     * @return static
     */
    public static function instance(WordListFactory $factory, LoggerInterface $logger = null)
    {
        return new static($factory, $logger);
    }

    /**
     * @param int $number
     * @return array
     */
    public static function letterOptions(int $number)
    {
        $output = [];
        foreach (self::PHONE[$number] as $letter) {
            if (!is_numeric($letter)) {
                $output[] = $letter;
            }
            if (!isset(self::LETTER_REPL[$letter])) {
                continue;
            }
            foreach (self::LETTER_REPL[$letter] as $repl) {
                $output[] = strtolower($repl);
            }
        }
        $output = array_unique($output);
        sort($output);

        return array_values($output);
    }

    /**
     * @param $phone_number
     * @return array
     */
    public function lookForAllWords($phone_number)
    {
        $words = [];
        for ($i = 0; $i < strlen($phone_number); $i++) {
            $new_phone_string = substr_replace($phone_number, str_repeat('*', $i), 0, $i);
            $this->logger->info("The start word was " . $new_phone_string);
            $new_words = $this->lookForWords($new_phone_string);
            $words = array_merge($words, $new_words);
        }

        return $this->restoreOriginalNumbers(
            $phone_number,
            $this->finalOrdering(
                $this->combineWordsAndOrder(
                    $this->decodeFindingsRaw($phone_number, $words)
                )
            ),
            '%d'
        );
    }

    /**
     * @param      $word
     * @param null $start
     * @return array
     */
    public function lookForWords($word, $start = null, $max_length = null)
    {
        $output = [];
        $this->logger->debug('look for words ' . $start . ' (' . $word . ')');

        // set only the first time (recursive)
        if (is_null($max_length)) {
            if (preg_match('/^([*]+)(\d+)$/', $word, $matches)) {
                $this->global_prefix = $matches[1];
                $word = $matches[2];
            } else {
                $this->global_prefix = '';
            }
            $this->word_length = $max_length = strlen($word);
        }

        // last letter to add to word (don't recurse)
        if (strlen($word) == 1) {
            foreach (self::letterOptions($word) as $letter) {
                $full_word = $start . $letter;
                $word_len = strlen($full_word);
                try {
                    if ($this->wordList($word_len)->containsWord($full_word)) {
                        $output[] = $this->global_prefix . $full_word;
                    }
                } catch (PhoneSpellException $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        } else {
            $first = current(str_split($word));
            foreach (self::letterOptions($first) as $letter) {
                $rest = substr($word, 1, strlen($word));
                $partial_word = $start . $letter;
                $word_len = strlen($partial_word);
                try {
                    if ($this->wordList($word_len)->containsWord($partial_word)) {
                        $output[] = $this->global_prefix . str_pad($partial_word, $this->word_length, '*');
                    }
                } catch (PhoneSpellException $e) {
                    $this->logger->error($e->getMessage());
                }

                // if first letter, just recurse. Don't check for matching longer words and save it
                if ($word_len == 1) {
                    $output = array_merge($output, self::lookForWords($rest, $partial_word, $this->word_length));
                } else {
                    // test longer words if they start with these letters... if not, don't recurse
                    for ($i = $max_length; $i > $word_len; $i--) {
                        try {
                            if ($this->wordList($i)->containsStartsWith($partial_word)) {
                                // only recurse if we have a potential full word match (longest only)
                                $this->logger->debug('look for more words ' . $rest . ' ' . $partial_word . ' ' . $i);
                                $output = array_merge($output, self::lookForWords($rest, $partial_word, $i));
                                break;
                            }
                        } catch (PhoneSpellException $e) {
                            $this->logger->error($e->getMessage());
                        }
                    }
                }
            }
        }

        return $output;
    }

    /**
     * @param string $phone_number
     * @param array  $findings
     * @param string $pattern
     * @return array
     */
    public function restoreOriginalNumbers($phone_number, array $findings, $pattern = '[%d]')
    {
        $original = str_split($phone_number);
        $output = [];

        // fill in the blanks with original numbers
        foreach ($findings as $found) {
            $found_array = str_split($found);
            foreach ($found_array as $i => &$letter) {
                if ($letter == '*') {
                    $letter = sprintf($pattern, $original[$i]);
                    //$letter = '[' . $original[$i] . ']';
                }
            }
            $output[] = implode($found_array);
        }

        return $output;
    }

    /**
     * @param int $num
     * @return WordListFilter
     * @throws PhoneSpellException
     */
    private function wordList(int $num)
    {
        if (!isset($this->word_lists[$num])) {
            $this->word_lists[$num] = $this->factory->makeByNum($num);
        }

        return $this->word_lists[$num];
    }
}
