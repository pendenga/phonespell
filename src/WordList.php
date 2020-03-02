<?php

namespace Pendenga\PhoneSpell;

use Psr\Log\LoggerInterface;

/**
 * Class WordList
 * @package Pendenga\PhoneSpell
 */
class WordList extends BinarySearch implements WordListInterface
{
    /**
     * @var array
     */
    protected $static_word_list;

    /**
     * WordList constructor.
     * @param array           $word_list
     * @param LoggerInterface $logger
     */
    public function __construct(array $word_list, LoggerInterface $logger = null)
    {
        $this->static_word_list = $word_list;
        parent::__construct($logger);
    }

    /**
     * @inheritDoc
     */
    public function containsStartsWith($word)
    {
        $this->logger->debug('incr', ['key' => 'starts with cmp']);
        return $this->binarySearch($word, $this->words(), WordListFilter::boolStartsWithBool());
    }

    /**
     * @inheritDoc
     */
    public function containsWord($word)
    {
        $this->logger->debug('incr', ['key' => 'containsWord cmp']);
        return $this->binarySearch($word, $this->words());
    }

    /**
     * @inheritDoc
     */
    public function count() {
        return count($this->words());
    }

    /**
     * @param array           $word_list
     * @param LoggerInterface $logger
     * @return static
     */
    public static function instance(array $word_list, LoggerInterface $logger = null)
    {
        return new static($word_list, $logger);
    }

    /**
     * @inheritDoc
     */
    public function words()
    {
        return $this->static_word_list;
    }
}
