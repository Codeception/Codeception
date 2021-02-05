<?php

declare(strict_types=1);

namespace Codeception\Extension;

use Codeception\Event\FailEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Subscriber\Console;

/**
 * DotReporter provides less verbose output for test execution.
 * Like PHPUnit printer it prints dots "." for successful testes and "F" for failures.
 *
 * ![](https://cloud.githubusercontent.com/assets/220264/26132800/4d23f336-3aab-11e7-81ba-2896a4c623d2.png)
 *
 * ```bash
 *  ..........
 *  ..........
 *  ..........
 *  ..........
 *  ..........
 *  ..........
 *  ..........
 *  ..........
 *
 * Time: 2.07 seconds, Memory: 20.00MB
 *
 * OK (80 tests, 124 assertions)
 * ```
 *
 *
 * Enable this reporter with `--ext option`
 *
 * ```
 * codecept run --ext DotReporter
 * ```
 *
 * Failures and Errors are printed by a standard Codeception reporter.
 * Use this extension as an example for building custom reporters.
 */
class DotReporter extends Extension
{
    /**
     * @var Console
     */
    protected $standardReporter;
    /**
     * @var array
     */
    protected $errors = [];
    /**
     * @var array
     */
    protected $failures = [];
    /**
     * @var int
     */
    protected $width = 10;
    /**
     * @var int
     */
    protected $currentPos = 0;

    public function _initialize(): void
    {
        $this->options['silent'] = false; // turn on printing for this extension
        $this->_reconfigure(['settings' => ['silent' => true]]); // turn off printing for everything else
        $this->standardReporter = new Console($this->options);
        $this->width = $this->standardReporter->detectWidth();
    }

    /**
     * We are listening for events
     *
     * @var array<string, string>
     */
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_SUCCESS => 'success',
        Events::TEST_FAIL    => 'fail',
        Events::TEST_ERROR   => 'error',
        Events::TEST_SKIPPED => 'skipped',
        Events::TEST_FAIL_PRINT => 'printFailed'
    ];

    public function beforeSuite(): void
    {
        $this->writeln('');
    }

    public function success(): void
    {
        $this->printChar('.');
    }

    public function fail(FailEvent $event): void
    {
        $this->printChar('<error>F</error>');
    }

    public function error(FailEvent $event): void
    {
        $this->printChar('<error>E</error>');
    }

    public function skipped(): void
    {
        $this->printChar('S');
    }

    protected function printChar($char): void
    {
        if ($this->currentPos >= $this->width) {
            $this->writeln('');
            $this->currentPos = 0;
        }
        $this->write($char);
        ++$this->currentPos;
    }

    public function printFailed(FailEvent $event): void
    {
        $this->standardReporter->printFail($event);
    }
}
