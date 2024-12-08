<?php

declare(strict_types=1);

namespace Codeception\Extension;

use Codeception\Event\FailEvent;
use Codeception\Event\PrintResultEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Subscriber\Console as CodeceptConsole;

/**
 * DotReporter provides less verbose output for test execution.
 * Like PHPUnit printer it prints dots "." for successful tests and "F" for failures.
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
    protected ?CodeceptConsole $standardReporter = null;

    protected array $errors = [];

    protected array $failures = [];

    protected int $width = 10;

    protected int $currentPos = 0;

    public function _initialize(): void
    {
        $this->_reconfigure(['settings' => ['silent' => true]]); // turn off printing for everything else
        $this->standardReporter = new CodeceptConsole($this->options);
        $this->width = $this->standardReporter->detectWidth();
    }

    /**
     * We are listening for events
     *
     * @var array<string, string>
     */
    public static array $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_SUCCESS => 'success',
        Events::TEST_FAIL    => 'fail',
        Events::TEST_ERROR   => 'error',
        Events::TEST_SKIPPED => 'skipped',
        Events::TEST_FAIL_PRINT => 'printFailed',
        Events::RESULT_PRINT_AFTER => 'afterResult',
    ];

    public function beforeSuite(): void
    {
        $this->output->writeln('');
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

    protected function printChar(string $char): void
    {
        if ($this->currentPos >= $this->width) {
            $this->output->writeln('');
            $this->currentPos = 0;
        }
        $this->write($char);
        ++$this->currentPos;
    }

    public function printFailed(FailEvent $event): void
    {
        $this->standardReporter->printFail($event);
    }

    public function afterResult(PrintResultEvent $event): void
    {
        $this->output->writeln('');
        $this->output->writeln('');
        $this->standardReporter->afterResult($event);
    }
}
