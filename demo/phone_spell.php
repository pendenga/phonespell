<?php

use Pendenga\PhoneSpell\Dictionary;
use Pendenga\PhoneSpell\PhoneSpell;
use Pendenga\PhoneSpell\Test\CountLogger;
use Pendenga\PhoneSpell\WordListFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$input_phone_number = $argv[1] ?? '593563';
$input_result_count = $argv[2] ?? 10;

$logger = new CountLogger();
try {
    $wlf = WordListFactory::instance(Dictionary::instance($logger), $logger);

    $results = PhoneSpell::instance($wlf, $logger)->lookForAllWords($input_phone_number);
    print "Found " . count($results) . " words within '{$input_phone_number}'. TOP {$input_result_count}: \n";
    print_r(array_slice($results, 0, $input_result_count));
    print "Metrics on calculations done: \n";
    print_r($logger->debug);
} catch (Exception $e) {
    print $e->getMessage();
}
