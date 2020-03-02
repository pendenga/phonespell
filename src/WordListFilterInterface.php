<?php

namespace Pendenga\PhoneSpell;

/**
 * Interface WordListInterface
 * @package Pendenga\PhoneSpell
 */
interface WordListFilterInterface
{
    /**
     * @param \Closure $bool_closure
     * @return $this
     */
    public function filter(\Closure $bool_closure);

    /**
     * Count words on the filtered list
     * @return int
     */
    public function count();

    /**
     * Apply a ends-with-$letters filter to word list
     * @param $letters
     * @return $this
     */
    public function filterEndsWith($word);

    /**
     * Apply a character length filter to word list
     * @param int $min
     * @param int $max
     * @return $this
     */
    public function filterLength($min = 1, $max = 12);

    /**
     * Apply a starts-with-$letters filter to word list
     * @param $letters
     * @return $this
     */
    public function filterStartsWith($word);

    /**
     * Reset any filters on the word list
     * @return $this
     */
    public function reset();

    /**
     * Return all words on the filtered list
     * @return array
     */
    public function words();
}
