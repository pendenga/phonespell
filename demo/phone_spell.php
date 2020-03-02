<?php

use Pendenga\PhoneSpell\Dictionary;
use Pendenga\PhoneSpell\PhoneSpell;
use Pendenga\PhoneSpell\Test\CountLogger;
use Pendenga\PhoneSpell\WordListFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$logger = new CountLogger();
try {
    $wlf = WordListFactory::instance(Dictionary::instance($logger), $logger);

    $results = PhoneSpell::instance($wlf, $logger)->lookForAllWords('593563');
    print "Found " . count($results) . " words in 593563. TOP 10: \n";
    print_r(array_slice($results, 0, 10));
    print "Metrics on calculations done: \n";
    print_r($logger->debug);
} catch (Exception $e) {
    print $e->getMessage();
}
