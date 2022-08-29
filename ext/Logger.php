<?php

declare(strict_types=1);

namespace Codeception\Extension;

use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Codeception\Test\Descriptor;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;

use function class_exists;
use function function_exists;
use function str_replace;
use function ucfirst;

/**
 * Log suites/tests/steps using Monolog library.
 * Monolog should be installed additionally by Composer.
 *
 * ```
 * composer require monolog/monolog
 * ```
 *
 * Codeception's core/internal stuff is logged into `tests/_output/codeception.log`.
 * Test suites' steps are logged into `tests/_output/<test_full_name>-<rotation_date>.log`.
 *
 * To enable this module add to your `codeception.yml`:
 *
 * ``` yaml
 * extensions:
 *     enabled: [Codeception\Extension\Logger]
 * ```
 *
 * #### Config
 *
 * * `max_files` (default: 3) - how many log files to keep
 *
 */
class Logger extends Extension
{
    /**
     * @var array<string, string>
     */
    public static array $events = [
        Events::SUITE_BEFORE    => 'beforeSuite',
        Events::TEST_BEFORE     => 'beforeTest',
        Events::TEST_AFTER      => 'afterTest',
        Events::TEST_END        => 'endTest',
        Events::STEP_BEFORE     => 'beforeStep',
        Events::TEST_FAIL       => 'testFail',
        Events::TEST_ERROR      => 'testError',
        Events::TEST_INCOMPLETE => 'testIncomplete',
        Events::TEST_SKIPPED    => 'testSkipped',
    ];

    protected ?RotatingFileHandler $logHandler = null;

    protected static ?\Monolog\Logger $logger = null;

    protected ?string $path = null;

    /**
     * @var array<string, int>
     */
    protected array $config = ['max_files' => 3];

    public function _initialize(): void
    {
        if (!class_exists('\Monolog\Logger')) {
            throw new ConfigurationException('Logger extension requires Monolog library to be installed');
        }
        $this->path = $this->getLogDir();

        // internal log
        $logHandler = new RotatingFileHandler($this->path . 'codeception.log', $this->config['max_files']);

        $formatter = $logHandler->getFormatter();
        if ($formatter instanceof LineFormatter) {
            $formatter->ignoreEmptyContextAndExtra(true);
        }

        self::$logger = new \Monolog\Logger('Codeception');
        self::$logger->pushHandler($logHandler);
    }

    public static function getLogger(): \Monolog\Logger
    {
        return self::$logger;
    }

    public function beforeSuite(SuiteEvent $event): void
    {
        $suiteLogFile = str_replace('\\', '_', $event->getSuite()->getName()) . '.log';
        $this->logHandler = new RotatingFileHandler($this->path . $suiteLogFile, $this->config['max_files']);
    }

    public function beforeTest(TestEvent $event): void
    {
        self::$logger = new \Monolog\Logger(Descriptor::getTestFullName($event->getTest()));
        self::$logger->pushHandler($this->logHandler);
        self::$logger->info('------------------------------------');
        self::$logger->info('STARTED: ' . ucfirst(Descriptor::getTestAsString($event->getTest())));
    }

    public function afterTest(TestEvent $event): void
    {
    }

    public function endTest(TestEvent $event): void
    {
        self::$logger->info('PASSED');
    }

    public function testFail(FailEvent $event): void
    {
        self::$logger->alert($event->getFail()->getMessage());
        self::$logger->info('# FAILED #');
    }

    public function testError(FailEvent $event): void
    {
        self::$logger->alert($event->getFail()->getMessage());
        self::$logger->info('# ERROR #');
    }

    public function testSkipped(FailEvent $event): void
    {
        self::$logger->info('# Skipped #');
    }

    public function testIncomplete(FailEvent $event): void
    {
        self::$logger->info('# Incomplete #');
    }

    public function beforeStep(StepEvent $event): void
    {
        self::$logger->info((string) $event->getStep());
    }
}
