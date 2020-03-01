<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Pendenga\PhoneSpell\Dictionary;
use Pendenga\PhoneSpell\PhoneSpell;
use Pendenga\PhoneSpell\Test\CountLogger;
use Pendenga\PhoneSpell\Test\EchoLogger;
use Pendenga\PhoneSpell\WordList;
use Pendenga\PhoneSpell\WordListFactory;
use Psr\Log\NullLogger;

//$words = WordList::instance(new EchoLogger())->words();

// testing a manual callback function
$length_2_5 = function ($word) {
    return (strlen($word) <= 5 && strlen($word) >= 2);
};

// $words = WordList::instance(new EchoLogger())->filter($length_2_5)->words();
// print "I got " . count($words) . " words as an array\n";
$logger = new CountLogger();
try {
    //$wl = WordList::instance(Dictionary::instance($logger), $logger)->byLength(3, 3);
    //$wl = WordList::instance(Dictionary::instance($logger), $logger);
    // $words = $wl->words();
    // print "I got " . count($words) . " words as an array\n";
    // print "The 5th word is {$words[4]}\n";

    // print ($wl->exists('icy') ? "Have word 'icy'\n" : "Not found 'icy'\n");
    // print ($wl->exists('it') ? "Have word 'it'\n" : "Not found 'it'\n");


    $wlf = WordListFactory::instance(Dictionary::instance($logger), $logger);
    //$spell = PhoneSpell::instance($wlf, $logger)->allPermutations('593563');
    // 51839 total permutations (2591 without numbers)
    $spell = PhoneSpell::instance($wlf, $logger);

    // $spell->lookForWords('593563');
    // print "The start word was: 593563\n";
    // $spell->lookForWords('*93563');
    // print "The start word was: *93563\n";
    // $spell->lookForWords('**3563');
    // print "The start word was: **3563\n";
    // $spell->lookForWords('***563');
    // print "The start word was: ***563\n";
    // $spell->lookForWords('****63');
    // print "The start word was: ****63\n";
    // $spell->lookForWords('*****3');
    // print "The start word was: *****3\n";
    // $spell->decodeFindings('593563');

    $spell->lookForWords('154729');
    print "The start word was: 154729\n";
    $spell->lookForWords('*54729');
    print "The start word was: *54729\n";
    $spell->lookForWords('**4729');
    print "The start word was: **4729\n";
    $spell->lookForWords('***729');
    print "The start word was: ***729\n";
    $spell->lookForWords('****29');
    print "The start word was: ****29\n";
    $spell->lookForWords('*****9');
    print "The start word was: *****9\n";
    $spell->decodeFindings('154729');

    print_r($spell->count());
    print_r($spell->findings());
    print_r($spell->results());
    print_r($logger->debug);
} catch (Exception $e) {
    print $e->getMessage();
}
// Array
// (
//     [finding] => 2
//     [results] => 51840
// )
// Array
// (
//     [exists_cmp] => 10710
//     [binarySearch] => 119287

// [0] => jod
// [1] => joe
// [2] => jof
// [3] => lod
// [4] => loe
// [5] => lof
