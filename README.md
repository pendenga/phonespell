# Phone Spell

Spell words with a phone number. This library contains word lists and functions necessary to efficiently find 
words hidden within phone numbers so you can more easily remember them. This project was inspired when we got 
a new conference bridge system at work, and I have to remember a six-digit conference line now instead of the 
old one-digit line. 

## Installation

This package is hosted on packagist installable via [Composer][link-composer].

### Requirements

- PHP version 7.1 or greater (7.2+ recommended)
- Composer (for installation)

### Installing Via Composer

Run the following at the command line in your repo: 
```bash
composer require pendenga/phonespell
```

Or add the following lines to your composer.json file...

```json
"require": {
  "pendenga/phonespell": "0.1.0",
},
```

and run the following command:

```bash
$ composer update
```

This will set the **Pendenga PhoneSpell** as a dependency in your project and install it.

When bootstrapping your application, you will need to require `'vendor/autoload.php'` in order to setup autoloading.

## Usage Example

```php
use Pendenga\PhoneSpell\Dictionary;
use Pendenga\PhoneSpell\PhoneSpell;
use Pendenga\PhoneSpell\WordListFactory;
use Psr\Log\NullLogger;

$logger = new NullLogger();
$wlf = WordListFactory::instance(Dictionary::instance($logger), $logger);

$results = PhoneSpell::instance($wlf, $logger)->lookForAllWords('593563');

print "Top 10 Results: \n";
print_r(array_slice($results, 0, 10));
```

[link-composer]: https://getcomposer.org/
