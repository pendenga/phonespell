<?php

namespace Pendenga\PhoneSpell;

/**
 * Interface WordListInterface
 * @package Pendenga\PhoneSpell
 */
interface WordListInterface
{
    // /**
    //  * @param \Closure $bool_closure
    //  * @return $this
    //  */
    // public function filter(\Closure $bool_closure);

    /**
     * Count words on the filtered list
     * @return int
     */
    public function count();

    // /**
    //  * Apply a ends-with-$letters filter to word list
    //  * @param $letters
    //  * @return $this
    //  */
    // public function endsWith($word);

    // /**
    //  * Apply a character length filter to word list
    //  * @param int $min
    //  * @param int $max
    //  * @return $this
    //  */
    // public function filterbyLength($min = 1, $max = 12);
    //
    // /**
    //  * Apply a starts-with-$letters filter to word list
    //  * @param $letters
    //  * @return $this
    //  */
    // public function filterByStartsWith($word);

    /**
     * Test if a word is found in the filtered list
     * @param $word
     * @return bool
     */
    public function hasWord($word);

    /**
     * Test whether our list contains any words starting with this word
     * @param $word
     * @return boolean
     */
    public function hasStartsWith($word);

    // /**
    //  * Reset any filters on the word list
    //  * @return $this
    //  */
    // public function reset();

    /**
     * Return all words on the filtered list
     * @return array
     */
    public function words();
}
