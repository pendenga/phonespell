<?php

namespace Pendenga\PhoneSpell;

/**
 * Interface WordListInterface
 * @package Pendenga\PhoneSpell
 */
interface WordListInterface
{
    /**
     * Count words on the filtered list
     * @return int
     */
    public function count();

    /**
     * Test if a word is found in the filtered list
     * @param $word
     * @return bool
     */
    public function containsWord($word);

    /**
     * Test whether our list contains any words starting with this word
     * @param $word
     * @return boolean
     */
    public function containsStartsWith($word);

    /**
     * Return all words on the filtered list
     * @return array
     */
    public function words();
}
