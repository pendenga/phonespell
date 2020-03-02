<?php

namespace Pendenga\PhoneSpell\Test;

use Pendenga\PhoneSpell\BinarySearch;
use Pendenga\PhoneSpell\WordListFilter;
use PHPUnit\Framework\TestCase;

/**
 * Class BinarySearchTest
 * @package Pendenga\PhoneSpell\Test
 */
class BinarySearchTest extends TestCase
{
    const WORD_LIST = [
        'aardvark',
        'aback',
        'abacus',
        'abalone',
        'abandon',
        'abase',
        'abash',
        'abate',
        'abater',
        'abbas',
        'abbe',
        'abbey',
        'abbot',
        'abbreviate',
        'abc',
        'abdicate',
        'abdomen',
        'abdominal',
        'abduct',
        'abed',
        'aberrant',
        'aberrate',
        'abet',
        'abetted',
        'abetting',
    ];

    /**
     * @dataProvider dataBinarySearch
     */
    public function testBinarySearch($expected, $word, $count)
    {
        $logger = new CountLogger();
        $bs = new BinarySearch($logger);
        $this->assertEquals($expected, $bs->binarySearch($word, self::WORD_LIST));
        $this->assertEquals($count, $logger->debug['binary search']);
    }

    /**
     * @return array
     */
    public function dataBinarySearch()
    {
        return [
            [true, 'abacus', 3],
            [true, 'abetting', 5],
            [true, 'abdicate', 3],
            [false, 'zillow', 5],
        ];
    }

    /**
     * @dataProvider dataStartsWith
     */
    public function testStartsWith($expected, $word, $count)
    {
        $logger = new CountLogger();
        $bs = new BinarySearch($logger);
        $this->assertEquals($expected, $bs->binarySearch($word, self::WORD_LIST, WordListFilter::boolStartsWithBool()));
        $this->assertEquals($count, $logger->debug['binary search']);
    }

    /**
     * @return array
     */
    public function dataStartsWith()
    {
        return [
            // don't match on whole word
            [false, 'abacus', 5],
            [false, 'abetting', 5],
            [false, 'abdicate', 5],
            [false, 'zillow', 5],
            // starts with
            [true, 'ab', 1],
            [true, 'abacu', 3],
            [false, 'abdicated', 4],
            [false, 'xa', 5],
        ];
    }
}
