<?php

namespace Pendenga\PhoneSpell\Test;

use Psr\Log\NullLogger;

/**
 * Class CountLogger
 * @package Gpsi\Portal\Test\Mock
 */
class CountLogger extends NullLogger
{
    /**
     * @var array
     */
    public $debug = [];

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = array())
    {
        if ($message == '') {
            $this->debug = array_merge($this->debug, $context);
        } elseif ($message == 'incr') {
            $this->debug[$context['key']]++;
        } else {
            $this->log('debug', $message, $context);
        }
    }
}
