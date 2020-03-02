<?php

use Pendenga\PhoneSpell\Dictionary;
use Pendenga\PhoneSpell\PhoneSpell;
use Pendenga\PhoneSpell\Test\CountLogger;
use Pendenga\PhoneSpell\WordListFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$logger = new CountLogger();
$words = PhoneSpell::instance(
    WordListFactory::instance(
        Dictionary::instance($logger),
        $logger
    ),
    $logger
)->allPermutations('593563');
print "found " . count($words) . " total permutations\n";
//print_r($words);
