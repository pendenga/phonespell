<?php

namespace Pendenga\PhoneSpell\Test;

use Pendenga\PhoneSpell\WordListFilter;
use PHPUnit\Framework\TestCase;

class WordListFilterTest extends TestCase
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

    // public function testBoolStartsWith()
    // {
    //
    // }

    /**
     * @dataProvider dataBoolEndsWith
     */
    public function testBoolEndsWith($expected, $haystack, $needle)
    {
        $wlf = WordListFilter::instance([]);
        $this->assertEquals($expected, WordListFilter::boolEndsWith($haystack, $needle));
    }

    /**
     * @return array
     */
    public function dataBoolEndsWith() {
        return [
            [true, 'abbreviate', 'ate'],
            [false, 'abc', 'ate'],
            [true, 'abdicate', 'ate'],
            [false, 'abdomen', 'ate'],
            [false, 'abdominal', 'ate'],
            [false, 'abduct', 'ate'],
            [false, 'abed', 'ate'],
            [false, 'aberrant', 'ate'],
            [true, 'aberrate', 'ate'],
        ];
    }

    /**
     * @dataProvider datafilterEndsWith
     */
    public function testfilterEndsWith($expected, $word)
    {
        $wlf = WordListFilter::instance(self::WORD_LIST)->filterEndsWith($word);
        $this->assertEquals($expected, $wlf->words());
    }

    /**
     * @return array
     */
    public function datafilterEndsWith() {
        return [
            [['abate', 'abbreviate', 'abdicate', 'aberrate'], 'ate'],
        ];
    }
}
