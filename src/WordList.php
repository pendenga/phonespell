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
    private $static_word_list;

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
    public function count() {
        return count($this->static_word_list);
    }

    /**
     * @inheritDoc
     */
    public function hasStartsWith($word)
    {
        $this->logger->debug('incr', ['key' => 'starts with cmp']);
        return $this->binarySearch($word, $this->static_word_list, $this->startsWithClosure());
    }

    /**
     * @inheritDoc
     */
    public function hasWord($word)
    {
        $this->logger->debug('incr', ['key' => 'hasWord cmp']);
        return $this->binarySearch($word, $this->static_word_list);
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
