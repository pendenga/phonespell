<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Pendenga\PhoneSpell\Dictionary;
use Pendenga\PhoneSpell\PhoneSpell;
use Pendenga\PhoneSpell\Test\CountLogger;
use Pendenga\PhoneSpell\Test\EchoLogger;
use Pendenga\PhoneSpell\WordListFilter;
use Pendenga\PhoneSpell\WordListFactory;
use Psr\Log\NullLogger;

$logger = new CountLogger();
try {
    $factory = WordListFactory::instance(Dictionary::instance($logger), $logger);
    $wl = $factory->make();
    $words = $wl->words();
    $word_count = count($words);
    print "I got " . count($words) . " words as an array\n";
    print "The 5th word is {$words[4]}\n";
    print "---\n";

    print ($wl->containsWord('icy') ? "Have word 'icy'\n" : "No words starting with 'icy'\n");
    print ($wl->containsWord('it') ? "Have word 'it'\n" : "No words starting with 'it'\n");
    print ($wl->containsWord('zyzwycki') ? "Have word 'zyzwycki'\n" : "No words starting with 'zyzwycki'\n");
    print "---\n";

    print ($wl->containsStartsWith('icy') ? "Have word starting with 'icy'\n" : "No words starting with 'icy'\n");
    print ($wl->containsStartsWith('it') ? "Have word starting 'it'\n" : "No words starting with 'it'\n");
    print ($wl->containsStartsWith('zyzwycki')
        ? "Have word starting with 'zyzwycki'\n"
        : "No words starting with 'zyzwycki'\n");
    print "---\n";

    printf(
        "Those 6 searches took an average of %s searches on a list of %s words\n",
        number_format($logger->debug['binary search'] / 6, 1),
        number_format($word_count)
    );
    print_r($logger->debug);

} catch (Exception $e) {
    print $e->getMessage();
}
