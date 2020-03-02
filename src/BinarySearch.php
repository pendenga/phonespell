<?php

/**
 * Based on Mannion007\PhpBinarySearch
 */
namespace Pendenga\PhoneSpell;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function PHPUnit\Framework\isNull;

/**
 * Class ArraySearch
 * @package Pendenga\PhoneSpell
 */
class BinarySearch
{
    use LoggerAwareTrait;

    /**
     * WordList constructor.
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * @param $needle
     * @param array $haystack
     * @param $compare
     * @param $high
     * @param int $low
     * @param bool $containsDuplicates
     * @return bool|int
     */
    public function binarySearch(
        $needle,
        array $haystack,
        $compare = 'strcmp',
        $high = null,
        $low = 0,
        $containsDuplicates = false
    ) {
        if (is_null($high)) {
            $high = count($haystack) - 1;
        }
        $key = false;
        while ($high >= $low) {
            $mid = (int)floor(($high + $low) / 2);
            $this->logger->debug('incr', ['key' => 'binary search']);
            $cmp = call_user_func($compare, $needle, $haystack[$mid]);
            if ($cmp < 0) {
                $high = $mid - 1;
            } elseif ($cmp > 0) {
                $low = $mid + 1;
            } else {
                if ($containsDuplicates) {
                    while ($mid > 0 && call_user_func($compare, $haystack[($mid - 1)], $haystack[$mid]) === 0) {
                        $mid--;
                    }
                }
                $key = $mid;
                break;
            }
        }
        return $key;
    }

    /**
     * Do a strcmp-style comparison, but match on partial starts-with string
     * Usage: $this->binarySearch($word, [WORD_LIST], $this->startsWith())
     * @return \Closure
     */
    public function boolStartsWithBool() {
        return function($needle, $haystack) {
            if (stripos($haystack, $needle) === 0) {
                if (strcmp($needle, $haystack) === 0) {
                    return -1;
                }
                return 0;
            }
            return strcmp($needle, $haystack);
        };
    }
}
