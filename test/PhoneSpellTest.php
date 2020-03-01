<?php

namespace Pendenga\PhoneSpell\Test;

use Pendenga\PhoneSpell\PhoneSpell;
use PHPUnit\Framework\TestCase;

/**
 * Class PhoneSpellTest
 * @package Pendenga\PhoneSpell\Test
 */
class PhoneSpellTest extends TestCase
{
    /**
     * @dataProvider dataLetterOptions
     */
    public function testLetterOptions($expected, $number)
    {
        $this->assertEquals($expected, PhoneSpell::letterOptions($number));
    }
    /**
     * @return array
     */
    public function dataLetterOptions()
    {
        return [
            [['o'], 0],
            [['i', 'l'], 1],
            [['a', 'b', 'c', 'k', 'x'], 2],
            [['d', 'e', 'f'], 3],
            [['a', 'g', 'h', 'i'], 4],
            [['j', 'k', 'l', 'x'], 5],
            [['m', 'n', 'o'], 6],
            [['p', 'q', 'r', 's', 't'], 7],
            [['b', 't', 'u', 'v'], 8],
            [['g', 'k', 'w', 'x', 'y', 'z'], 9],
        ];
    }
}
