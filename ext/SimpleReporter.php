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
        $this->output->writeln('');
    }

    public function success(): void
    {
        $this->output->write('[+] ');
    }

    public function fail(): void
    {
        $this->output->write('[-] ');
    }

    public function error(): void
    {
        $this->output->write('[E] ');
    }

    // we are printing test status and time taken
    public function after(TestEvent $event): void
    {
        $secondsInput = $event->getTime();
        // See https://stackoverflow.com/q/16825240
        $milliseconds = (int)($secondsInput * 1000);
        $seconds = (int)($milliseconds / 1000);
        $time = ($seconds % 60) . (($milliseconds === 0) ? '' : '.' . $milliseconds);

        $this->output->write(Descriptor::getTestSignature($event->getTest()));
        $this->output->writeln(' (' . $time . 's)');
    }
}
