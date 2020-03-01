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
     * @var WordList[]
     */
    protected $word_lists;

    /**
     * @var int
     */
    protected $word_length;
    protected $global_prefix = '';
    protected $raw_findings = [];
    protected $raw_results = [];

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
     * @return $this
     */
    public function allPermutations($word, $start = null)
    {
        $this->word_length = strlen($word);

        // $this->logger->debug(__METHOD__ . " with start: {$word} ({$start})");
        if (strlen($word) == 1) {
            foreach (self::letterOptions($word) as $letter) {
                $full_word = $start . $letter;
                $this->found($full_word);
                // if ($this->word_list->hasWord($full_word)) {
                //     $this->raw_findings[] = $full_word;
                // }
            }
        } else {
            $first = current(str_split($word));
            foreach (self::letterOptions($first) as $letter) {
                $rest = substr($word, 1, strlen($word));
                $partial_word = $start . $letter;
                // if ($this->word_list->hasWord($partial_word)) {
                //     $this->raw_findings[] = $partial_word;
                // }
                self::allPermutations($rest, $partial_word);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return [
            'finding' => count($this->raw_findings),
            'results' => count($this->raw_results),
        ];
    }

    /**
     * @param $original_word
     * @return array
     */
    public function decodeFindings($original_word)
    {
        $original = str_split($original_word);
        $output = [];
        $combined = [];
        foreach ($this->raw_findings as $found) {
            try {
                $new_word = ['word' => null, 'partial' => null, 'pre' => null, 'post' => null];
                foreach (str_split($found) as $i => $letter) {
                    if ($letter == '*') {
                        $new_word['word'] .= '*';
                        continue;
                    }
                    if (in_array($letter, self::PHONE[$original[$i]])) {
                        $new_word['word'] .= $letter;
                        $new_word['partial'] .= $letter;
                        continue;
                    }
                    if (isset(self::LETTER_REPL[$original[$i]])) {
                        if (in_array($letter, self::LETTER_REPL[$original[$i]])) {
                            $new_word['word'] .= $original[$i];
                            $new_word['partial'] .= $original[$i];
                            continue;
                        } elseif (in_array(strtoupper($letter), self::LETTER_REPL[$original[$i]])) {
                            $new_word['word'] .= $original[$i];
                            $new_word['partial'] .= $original[$i];
                            continue;
                        }
                    }

                    if (isset(self::LETTER_REPL[$letter])) {
                        foreach (self::LETTER_REPL[$letter] as $repl) {
                            if (in_array(strtolower($repl), self::PHONE[$original[$i]])) {
                                $new_word['word'] .= $repl;
                                $new_word['partial'] .= $repl;
                                continue;
                            }
                        }
                    }
                    throw new PhoneSpellException('letter not decoded ' . $letter . ' in ' . $found);
                }

                if (preg_match("/([*]*)[^*]*([*]*)/", $found, $matches)) {
                    $new_word['pre'] = $matches[1];
                    $new_word['post'] = $matches[2];
                }
                $new_word['score'] = pow(2, strlen($new_word['partial'])) -
                                     strlen($new_word['pre']) -
                                     strlen($new_word['post']);
                $output[] = $new_word;
                $combined[] = ['word' => $new_word['word'], 'score' => $new_word['score']];
            } catch (PhoneSpellException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        // try to combine words...
        foreach ($output as $foo) {
            foreach ($output as $bar) {
                if ($foo['word'] == $bar['word']) {
                    continue;
                }
                if (strlen($foo['post']) > strlen($bar['pre'])) {
                    continue;
                }
                if (strlen($foo['pre']) + strlen($foo['partial']) <= strlen($bar['pre'])) {
                    $try = str_pad($foo['pre'] . $foo['partial'], strlen($bar['pre']), '*') .
                           $bar['partial'] .
                           $bar['post'];
                    $combined[] = [
                        'word'  => $try,
                        'score' => pow(2, strlen($foo['partial'])) +
                                   pow(2, strlen($bar['partial'])) -
                                   strlen($foo['pre']) -
                                   strlen($bar['post']),
                    ];
                    continue;
                }
            }
        }

        // fill in the blanks with original numbers
        $scored = [];
        foreach ($combined as $found) {
            $found_array = str_split($found['word']);
            foreach ($found_array as $i => &$letter) {
                if ($letter == '*') {
                    $letter = '[' . $original[$i] . ']';
                }
            }
            $scored[$found['score']][] = implode($found_array);
        }
        krsort($scored);

        $this->raw_results = [];
        foreach ($scored as $score => $set) {
            $this->raw_results = array_merge($this->raw_results, $set);
        }
    }

    /**
     * @return array
     */
    public function findings()
    {
        return $this->raw_findings;
    }

    /**
     * @param $word
     * @return $this
     */
    public function found($word)
    {
        $this->logger->debug('found full word: ' . $this->global_prefix . $word);
        $this->raw_findings[] = $this->global_prefix . $word;

        return $this;
    }

    /**
     * @param $word
     * @return $this
     */
    public function foundPartial($word)
    {
        $this->logger->debug('found partial ' . $this->global_prefix . str_pad($word, $this->word_length, '*'));
        $this->raw_findings[] = $this->global_prefix . str_pad($word, $this->word_length, '*');

        return $this;
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
     * @param      $word
     * @param null $start
     * @return $this
     */
    public function lookForWords($word, $start = null, $max_length = null)
    {
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

        // $this->logger->debug(__METHOD__ . " with start: {$word} ({$start})");
        // last letter to add to word (don't recurse)
        if (strlen($word) == 1) {
            foreach (self::letterOptions($word) as $letter) {
                $full_word = $start . $letter;
                $word_len = strlen($full_word);
                try {
                    if ($this->wordList($word_len)->hasWord($full_word)) {
                        $this->found($full_word);
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
                    if ($this->wordList($word_len)->hasWord($partial_word)) {
                        $this->foundPartial($partial_word);
                    }
                } catch (PhoneSpellException $e) {
                    $this->logger->error($e->getMessage());
                }

                // if first letter, just recurse. Don't check for matching longer words and save it
                if ($word_len == 1) {
                    self::lookForWords($rest, $partial_word, $this->word_length);
                } else {
                    // test longer words if they start with these letters... if not, don't recurse
                    for ($i = $max_length; $i > $word_len; $i--) {
                        try {
                            if ($this->wordList($i)->hasStartsWith($partial_word)) {
                                // only recurse if we have a potential full word match (longest only)
                                $this->logger->debug('look for more words ' . $rest . ' ' . $partial_word . ' ' . $i);
                                self::lookForWords($rest, $partial_word, $i);
                                break;
                            }
                        } catch (PhoneSpellException $e) {
                            $this->logger->error($e->getMessage());
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function results()
    {
        return $this->raw_results;
    }

    /**
     * @param int $num
     * @return WordList
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
