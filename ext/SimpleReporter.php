<?php

declare(strict_types=1);

namespace Codeception\Extension;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;

/**
 * This extension demonstrates how you can implement console output of your own.
 * Recommended to be used for development purposes only.
 */
class SimpleReporter extends Extension
{
    public function _initialize(): void
    {
        $this->options['silent'] = false; // turn on printing for this extension
        $this->_reconfigure(['settings' => ['silent' => true]]); // turn off printing for everything else
    }

    /**
     * We are listening for events
     *
     * @var array<string, string>
     */
    public static array $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_END     => 'after',
        Events::TEST_SUCCESS => 'success',
        Events::TEST_FAIL    => 'fail',
        Events::TEST_ERROR   => 'error',
    ];

    public function beforeSuite(): void
    {
        $this->writeln('');
    }

    public function success(): void
    {
        $this->write('[+] ');
    }

    public function fail(): void
    {
        $this->write('[-] ');
    }

    public function error(): void
    {
        $this->write('[E] ');
    }

    // we are printing test status and time taken
    public function after(TestEvent $event): void
    {
        $secondsInput = $event->getTime();
        // See https://stackoverflow.com/q/16825240
        $seconds = ($milliseconds = (int)($secondsInput * 1000)) / 1000;
        $time = ($seconds % 60) . (($milliseconds === 0) ? '' : '.' . $milliseconds);

        $this->write(Descriptor::getTestSignature($event->getTest()));
        $this->writeln(' (' . $time . 's)');
    }
}
