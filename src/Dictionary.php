<?php

namespace Pendenga\PhoneSpell;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Dictionary
 * @package Pendenga\PhoneSpell
 */
class Dictionary
{
    use LoggerAwareTrait;
    const WORD_FILES = [
        'english.txt',
        'boy_names.txt',
        'girl_names.txt',
    ];

    const WORDS_1_LETTERS = 'WORDS_1_LETTERS';
    const WORDS_2_LETTERS = 'WORDS_2_LETTERS';
    const WORDS_3_LETTERS = 'WORDS_3_LETTERS';
    const WORDS_4_LETTERS = 'WORDS_4_LETTERS';
    const WORDS_5_LETTERS = 'WORDS_5_LETTERS';
    const WORDS_6_LETTERS = 'WORDS_6_LETTERS';
    const WORDS_7_LETTERS = 'WORDS_7_LETTERS';
    const WORDS_8_LETTERS = 'WORDS_8_LETTERS';
    const WORDS_9_LETTERS = 'WORDS_9_LETTERS';
    const WORDS_ALL       = 'WORDS_ALL';

    const WORD_LISTS = [
        self::WORDS_1_LETTERS,
        self::WORDS_2_LETTERS,
        self::WORDS_3_LETTERS,
        self::WORDS_4_LETTERS,
        self::WORDS_5_LETTERS,
        self::WORDS_6_LETTERS,
        self::WORDS_7_LETTERS,
        self::WORDS_8_LETTERS,
        self::WORDS_9_LETTERS,
        self::WORDS_ALL,
    ];

    /**
     * Index to find appropriate word list by word length
     */
    const FIND_LIST = [
        '1' => self::WORDS_1_LETTERS,
        '2' => self::WORDS_2_LETTERS,
        '3' => self::WORDS_3_LETTERS,
        '4' => self::WORDS_4_LETTERS,
        '5' => self::WORDS_5_LETTERS,
        '6' => self::WORDS_6_LETTERS,
        '7' => self::WORDS_7_LETTERS,
        '8' => self::WORDS_8_LETTERS,
        '9' => self::WORDS_9_LETTERS,
    ];

    static $static_word_list;
    static $ini;

    protected $active_word_list_key = self::WORDS_ALL;

    /**
     * Dictionary constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger ?? new NullLogger());
        if ($this->buildTest()) {
            $this->build();
        }
    }

    /**
     * @param string $word_list
     * @return string
     */
    private function buildPath($word_list = self::WORDS_ALL)
    {
        if (!isset(self::$ini)) {
            self::$ini = parse_ini_file(__DIR__ . '/../config.ini');
        }

        return self::$ini['tmp_directory'] . $word_list . '.txt';
    }

    /**
     * @return bool
     */
    private function buildTest()
    {
        $needs_build = false;
        foreach (self::WORD_LISTS as $word_list) {
            if (!file_exists($this->buildPath($word_list))) {
                $needs_build = true;
            }
        }
        $this->logger->info($needs_build ? 'build needed' : 'dictionaries ready');

        return $needs_build;
    }

    /**
     * @return $this
     */
    private function build()
    {
        foreach (self::WORD_FILES as $word_file) {
            $this->logger->info('LOADING WORD FILE: ' . $word_file);
            $word_list = file(__DIR__ . '/' . $word_file, FILE_IGNORE_NEW_LINES);
            foreach ($word_list as $word) {
                // always write to WORDS_ALL
                self::$static_word_list[self::WORDS_ALL][] = $word;

                // find length-appropriate list
                if ($word_list = self::FIND_LIST[strlen($word)]) {
                    self::$static_word_list[$word_list][] = $word;
                }
            }
        }

        // write all word lists to file
        foreach (self::WORD_LISTS as $word_list) {
            sort(self::$static_word_list[$word_list]);
            $this->logger->info('WRITING WORD LIST: ' . $word_list);
            file_put_contents($this->buildPath($word_list), implode("\n", self::$static_word_list[$word_list]));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getActiveKey() {
        return $this->active_word_list_key;
    }

    /**
     * Select a word list by the length of the word
     * @param int $num
     * @return string
     * @throws PhoneSpellException
     */
    public function getKeyByNum(int $num) {
        if (!isset(self::FIND_LIST[$num])) {
            throw new PhoneSpellException('undefined word length: ' . $num);
        }
        return self::FIND_LIST[$num];
    }

    /**
     * @param LoggerInterface|null $logger
     * @return static
     */
    public static function instance(LoggerInterface $logger = null)
    {
        return new static($logger);
    }

    /**
     * @param $word_list
     * @return $this
     */
    private function load($word_list = self::WORDS_ALL)
    {
        if (!isset(self::$static_word_list[$word_list])) {
            $this->logger->info('LOADING WORD LIST: ' . $word_list);
            $this->logger->debug('incr', ['key' => 'load word list']);
            self::$static_word_list[$word_list] = file($this->buildPath($word_list), FILE_IGNORE_NEW_LINES);
        }

        return $this;
    }

    /**
     * @param string $word_list
     * @return $this
     * @throws PhoneSpellException
     */
    public function setWordList($word_list = self::WORDS_ALL)
    {
        if (!in_array($word_list, self::WORD_LISTS)) {
            throw new PhoneSpellException('undefined word list: ' . $word_list);
        }
        if ($this->active_word_list_key != $word_list) {
            $this->logger->debug('setting word list ' . $word_list);
            $this->active_word_list_key = $word_list;
        }

        return $this;
    }

    /**
     * @param int $num
     * @return $this
     * @throws PhoneSpellException
     */
    public function setWordListByNum(int $num)
    {
        $word_list = $this->getKeyByNum($num);
        if ($word_list != $this->getActiveKey()) {
            $this->setWordList($word_list);
        }

        return $this;
    }

    /**
     * @param string $word_list
     * @return array
     */
    public function words()
    {
        $this->load($this->active_word_list_key);
        $this->logger->debug("getting word list ({$this->active_word_list_key})");

        return self::$static_word_list[$this->active_word_list_key];
    }
}
