<?php

namespace Pendenga\PhoneSpell;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class WordListFactory
 * @package Pendenga\PhoneSpell
 */
class WordListFactory
{
    use LoggerAwareTrait;

    /**
     * @var Dictionary
     */
    private $dictionary;

    /**
     * WordList constructor.
     * @param Dictionary           $dictionary
     * @param LoggerInterface|null $logger
     */
    public function __construct(Dictionary $dictionary, LoggerInterface $logger = null)
    {
        $this->dictionary = $dictionary;
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * @param Dictionary      $dictionary
     * @param LoggerInterface $logger
     * @return static
     */
    public static function instance(Dictionary $dictionary, LoggerInterface $logger = null)
    {
        return new static($dictionary, $logger);
    }

    /**
     * @return WordListFilter
     */
    public function make()
    {
        return new WordListFilter($this->dictionary->words(), $this->logger);
    }

    /**
     * @return WordListFilter
     */
    public function makeFilter()
    {
        return new WordListFilter($this->dictionary->words(), $this->logger);
    }

    /**
     * @param $num
     * @return WordListFilter
     * @throws PhoneSpellException
     */
    public function makeByNum($num)
    {
        $this->dictionary->setWordListByNum($num);

        return $this->make();
    }

    /**
     * @param $num
     * @return WordListFilter
     * @throws PhoneSpellException
     */
    public function makeFilterByNum($num)
    {
        $this->dictionary->setWordListByNum($num);

        return $this->makeFilter();
    }

    // /**
    //  * @param string $word_list
    //  * @return $this
    //  * @throws PhoneSpellException
    //  */
    // public function setList($word_list = Dictionary::WORDS_ALL)
    // {
    //     $this->dictionary->setWordList($word_list);
    //
    //     return $this;
    // }
    //
    // /**
    //  * @param $num
    //  * @return $this
    //  * @throws PhoneSpellException
    //  */
    // public function setListByNum($num)
    // {
    //     $this->dictionary->setWordListByNum($num);
    //
    //     return $this;
    // }

    /**
     * @param Dictionary $dictionary
     */
    public function setDictionary(Dictionary $dictionary)
    {
        $this->dictionary = $dictionary;
    }
}
