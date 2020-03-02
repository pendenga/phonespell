<?php

namespace Pendenga\PhoneSpell;

use Psr\Log\LoggerAwareTrait;

/**
 * Class WordListFilter
 * @package Pendenga\PhoneSpell
 */
class WordListFilter extends WordList implements WordListFilterInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    protected $temp_word_list;

    /**
     * @param     $word
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function boolByLength($word, $min, $max) {
        return (strlen($word) <= $max && strlen($word) >= $min);
    }

    /**
     * @inheritDoc
     */
    public static function boolStartsWith($haystack, $needle) {
        return stripos($haystack, $needle) === 0;
    }

    /**
     * @inheritDoc
     */
    public static function boolEndsWith($haystack, $needle) {
        return stripos($haystack, $needle) === strlen($haystack) - strlen($needle);
    }

    /**
     * @inheritDoc
     */
    public function filter(\Closure $bool_closure)
    {
        $this->load();
        $this->temp_word_list = array_filter($this->temp_word_list, $bool_closure);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function filterEndsWith($word)
    {
        $this->logger->info("filtering words ending with {$word}");
        $wrapper = function ($list_word) use ($word) {
            return self::boolEndsWith($list_word, $word);
        };

        return $this->filter($wrapper);
    }

    /**
     * @inheritDoc
     */
    public function filterLength($min = 1, $max = 12)
    {
        $this->logger->info("filtering words between {$min} and {$max}");
        $wrapper = function ($word) use ($min, $max) {
            return self::boolByLength($word, $min, $max);
        };

        return $this->filter($wrapper);
    }

    /**
     * @inheritDoc
     */
    public function filterStartsWith($word)
    {
        $this->logger->info("filtering words starting with {$word}");
        $wrapper = function ($list_word) use ($word) {
            return self::boolStartsWith($list_word, $word);
        };

        return $this->filter($wrapper);
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
        $this->temp_word_list = $this->static_word_list;

        return $this;
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
