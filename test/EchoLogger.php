<?php

namespace Pendenga\PhoneSpell\Test;

use Psr\Log\AbstractLogger;

/**
 * Class EchoLogger
 * @package Gpsi\Portal\Test\Mock
 */
class EchoLogger extends CountLogger
{
    /**
     * @var array
     */
    public $debug = [];

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = array())
    {
        echo $level . ' ' . $message . ' -> ' . json_encode($context) . "\n";
    }

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
