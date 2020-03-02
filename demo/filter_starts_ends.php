<?php

use Pendenga\PhoneSpell\Dictionary;
use Pendenga\PhoneSpell\PhoneSpellException;
use Pendenga\PhoneSpell\WordListFactory;
use Prophecy\Comparator\Factory;
use Psr\Log\NullLogger;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $logger = new NullLogger();
    $factory = WordListFactory::instance(Dictionary::instance($logger), $logger);
    $wlf = $factory->makeFilter();

    print "words before filter: " . count($wlf->words()) . "\n";
    print "words starting with 'gra': " . count($wlf->filterStartsWith('gra')->words()) . "\n";
    $wlf->reset();
    print "words ending with 'ing': " . count($wlf->filterEndsWith('ing')->words()) . "\n";
    print "words starting with 'gra' and ending with 'ing': " . count($wlf->filterStartsWith('gra')->words()) . "\n";
    print "---\n";
} catch (PhoneSpellException $e) {
    $logger->error($e->getMessage());
}

