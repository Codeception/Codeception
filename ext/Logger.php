<?php
namespace Codeception\Extension;

use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Codeception\Extension;
use Codeception\Test\Descriptor;
use Monolog\Handler\RotatingFileHandler;
use Codeception\Configuration as Config;
use Psr\Log\LoggerInterface;

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
    }

    public static function getLogger()
    {
        if (self::$logger) {
            $logger = self::$logger;
        } else {
            $logger = self::getDefaultLogger();
        }

        return $logger;
    }

    public static function getConfig()
    {
        $vars = get_class_vars(get_class());
        $config = $vars['config'];

        return $config;
    }

    public static function getDefaultLogger()
    {
        $path = Config::outputDir();
        $config = self::getConfig();

        $logHandler = new RotatingFileHandler($path . 'codeception.log', $config['max_files']);
        $logger = new \Monolog\Logger('Codeception');
        $logger->pushHandler($logHandler);
        return $logger;
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $config = self::getConfig();
        $suite = str_replace('\\', '_', $e->getSuite()->getName());
        self::getLogger()->logHandler = new RotatingFileHandler($this->path . $suite, $config['max_files']);
    }

    public function beforeTest(TestEvent $e)
    {
        $descriptor = Descriptor::getTestFileName($e->getTest());
        $newLogger = self::getLogger()->withName($descriptor);
        self::$logger = $newLogger;
        self::info('------------------------------------');
        self::info("STARTED: " . ucfirst(Descriptor::getTestAsString($e->getTest())));
    }

    public function afterTest(TestEvent $e)
    {
    }

    public function endTest(TestEvent $e)
    {
        self::info("PASSED");
        self::$logger = self::getDefaultLogger();
    }

    public function testFail(FailEvent $e)
    {
        self::alert($e->getFail()->getMessage());
        self::info("# FAILED #");
    }

    public function testError(FailEvent $e)
    {
        self::alert($e->getFail()->getMessage());
        self::info("# ERROR #");
    }

    public function testSkipped(FailEvent $e)
    {
        self::info("# Skipped #");
    }

    public function testIncomplete(FailEvent $e)
    {
        self::info("# Incomplete #");
    }

    public function beforeStep(StepEvent $e)
    {
        self::info((string)$e->getStep());
    }

    public static function emergency($message, array $context = array())
    {
        self::getLogger()->emergency($message, $context);
    }

    public static function alert($message, array $context = array())
    {
        self::getLogger()->alert($message, $context);
    }

    public static function critical($message, array $context = array())
    {
        self::getLogger()->critical($message, $context);
    }

    public static function error($message, array $context = array())
    {
        self::getLogger()->error($message, $context);
    }

    public static function warning($message, array $context = array())
    {
        self::getLogger()->warning($message, $context);
    }

    public static function notice($message, array $context = array())
    {
        self::getLogger()->emergency($message, $context);
    }

    public static function info($message, array $context = array())
    {
        self::getLogger()->info($message, $context);
    }

    public static function debug($message, array $context = array())
    {
        self::getLogger()->debug($message, $context);
    }

    public static function log($level, $message, array $context = array())
    {
        self::getLogger()->log($level, $message, $context);
    }
}
