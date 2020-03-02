<?php

use Pendenga\PhoneSpell\Dictionary;
use Pendenga\PhoneSpell\PhoneSpellException;
use Pendenga\PhoneSpell\WordListFactory;
use Psr\Log\NullLogger;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $logger = new NullLogger();
    $factory = WordListFactory::instance(Dictionary::instance($logger), $logger);

    // apply a custom filter
    $wlf = $factory->makeFilter();
    print "words before filter: " . count($wlf->words()) . "\n";
    print "words filtered to 2-5 letters: " . count($wlf->filterLength(2, 5)->words()) . "\n";

    // testing a manual callback function
    $length_2_5 = function ($word) {
        return (strlen($word) <= 5 && strlen($word) >= 2);
    };
    print "words filtered to 2-5 letters: " . count($wlf->filter($length_2_5)->words()) . "\n";
    print "---\n";

    // Two ways to get all words with 5 letters:
    // 1. Start wih the full list and use standard filter to bring it down to 5 letters
    print "words filtered by length(5): " . count($wlf->filterLength(5)->words()) . "\n";

    // 2. Start wih the full list and use custom filter to bring it down to 5 letters
    $length_5 = function ($word) {
        return (strlen($word) == 5);
    };
    print "words filtered to 5 letters: " . count($wlf->filter($length_5)->words()) . "\n";

    // 3. Load the dictionary with 5-letter words
    $wl = $factory->makeByNum(5);
    print "words in 5 letter word list: " . count($wl->words()) . "\n";
} catch (PhoneSpellException $e) {
    $logger->error($e->getMessage());
}

