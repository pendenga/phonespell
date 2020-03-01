<?php

namespace Pendenga\PhoneSpell;

use Psr\Log\LoggerAwareTrait;

/**
 * Class WordListFilter
 * @package Pendenga\PhoneSpell
 */
class WordListFilter extends BinarySearch
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    static $static_word_list;

    /**
     * @var array
     */
    protected $temp_word_list;

    /**
     * @inheritDoc
     */
    public function count() {
        return count($this->temp_word_list);
    }

    /**
     * @inheritDoc
     */
    public function filter(\Closure $bool_closure)
    {
        $this->load();
        $this->logger->info(' length: ' . count($this->temp_word_list));
        $this->temp_word_list = array_filter($this->temp_word_list, $bool_closure);
        $this->logger->info(' length: ' . count($this->temp_word_list));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function filterByEndsWith($word)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function filterByLength($min = 1, $max = 12)
    {
        $this->logger->info("filtering words between {$min} and {$max}");
        $that = $this;
        $wrapper = function ($word) use ($that, $min, $max) {
            return $that->testByLength($word, $min, $max);
        };

        return $this->filter($wrapper);
    }

    /**
     * @inheritDoc
     */
    public function filterByStartsWith($word)
    {
        $this->logger->info("filtering words between {$min} and {$max}");
        $that = $this;
        $wrapper = function ($word) use ($that, $min, $max) {
            return $that->testByLength($word, $min, $max);
        };

        return $this->filter($wrapper);
    }

    /**
     * @inheritDoc
     */
    public function hasWord($word)
    {
        $this->load();
        $this->logger->debug('incr', ['key' => 'hasWord cmp']);
        $this->logger->debug("{$word} has word? " . current($this->temp_word_list));
        return $this->binarySearch($word, $this->temp_word_list);
    }

    /**
     * @return $this
     */
    private function load()
    {
        if (!isset($this->temp_word_list)) {
            $this->reset();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->logger->info("loading words from static list");
        $this->logger->debug('incr', ['key' => 'reset word list']);
        $this->temp_word_list = self::$static_word_list;

        return $this;
    }

    /**
     * @param     $word
     * @param int $min
     * @param int $max
     * @return bool
     */
    public function testByLength($word, $min, $max) {
        return (strlen($word) <= $max && strlen($word) >= $min);
    }

    /**
     * @inheritDoc
     */
    public function words()
    {
        $this->load();

        // strip off the original keys
        return array_values($this->temp_word_list);
    }

}
