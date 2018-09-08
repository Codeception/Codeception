<?php
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
use Monolog\Handler\RotatingFileHandler;

/**
 * Log suites/tests/steps using Monolog library.
 * Monolog should be installed additionally by Composer.
 *
 * ```
 * composer require monolog/monolog
 * ```
 *
 * Steps are logged into `tests/_output/codeception.log`
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
    public static $events = [
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

    protected $logHandler;

    /**
     * @var \Monolog\Logger
     */
    protected static $logger;

    protected $path;

    protected $config = ['max_files' => 3];

    public function _initialize()
    {
        if (!class_exists('\Monolog\Logger')) {
            throw new ConfigurationException("Logger extension requires Monolog library to be installed");
        }
        $this->path = $this->getLogDir();

        // internal log
        $logHandler = new RotatingFileHandler($this->path . 'codeception.log', $this->config['max_files']);
        self::$logger = new \Monolog\Logger('Codeception');
        self::$logger->pushHandler($logHandler);
    }

    public static function getLogger()
    {
        return self::$logger;
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $suite = str_replace('\\', '_', $e->getSuite()->getName());
        $this->logHandler = new RotatingFileHandler($this->path . $suite, $this->config['max_files']);
    }

    public function beforeTest(TestEvent $e)
    {
        self::$logger = new \Monolog\Logger(Descriptor::getTestFileName($e->getTest()));
        self::$logger->pushHandler($this->logHandler);
        self::$logger->info('------------------------------------');
        self::$logger->info("STARTED: " . ucfirst(Descriptor::getTestAsString($e->getTest())));
    }

    public function afterTest(TestEvent $e)
    {
    }

    public function endTest(TestEvent $e)
    {
        self::$logger->info("PASSED");
    }

    public function testFail(FailEvent $e)
    {
        self::$logger->alert($e->getFail()->getMessage());
        self::$logger->info("# FAILED #");
    }

    public function testError(FailEvent $e)
    {
        self::$logger->alert($e->getFail()->getMessage());
        self::$logger->info("# ERROR #");
    }

    public function testSkipped(FailEvent $e)
    {
        self::$logger->info("# Skipped #");
    }

    public function testIncomplete(FailEvent $e)
    {
        self::$logger->info("# Incomplete #");
    }

    public function beforeStep(StepEvent $e)
    {
        self::$logger->info((string) $e->getStep());
    }
}

if (!function_exists('codecept_log')) {
    function codecept_log()
    {
        return Logger::getLogger();
    }
} else {
    throw new ExtensionException('Codeception\Extension\Logger', "function 'codecept_log' already defined");
}
