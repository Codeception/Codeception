<?php

namespace Codeception\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\Configuration;
use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Monolog\Handler\RotatingFileHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Logger implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        CodeceptionEvents::SUITE_BEFORE    => 'beforeSuite',
        CodeceptionEvents::TEST_BEFORE     => 'beforeTest',
        CodeceptionEvents::TEST_AFTER      => 'afterTest',
        CodeceptionEvents::TEST_END        => 'endTest',
        CodeceptionEvents::STEP_BEFORE     => 'beforeStep',
        CodeceptionEvents::TEST_FAIL       => 'testFail',
        CodeceptionEvents::TEST_ERROR      => 'testError',
        CodeceptionEvents::TEST_INCOMPLETE => 'testIncomplete',
        CodeceptionEvents::TEST_SKIPPED    => 'testSkipped',
    ];

    protected $logHandler;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    protected $path;
    protected $maxFiles;

    public function __construct($maxFiles = 3)
    {
        $this->path     = Configuration::logDir();
        $this->maxFiles = $maxFiles;

        // internal log
        $logHandler   = new RotatingFileHandler($this->path . 'codeception.log', $this->maxFiles);
        $this->logger = new \Monolog\Logger('Codeception');
        $this->logger->pushHandler($logHandler);
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $suite            = str_replace('\\', '_', $e->getSuite()->getName());
        $this->logHandler = new RotatingFileHandler($this->path . $suite, $this->maxFiles);
    }

    public function beforeTest(TestEvent $e)
    {
        $this->logger = new \Monolog\Logger($e->getTest()->getFileName());
        $this->logger->pushHandler($this->logHandler);
    }

    public function afterTest(TestEvent $e)
    {
    }

    public function endTest(TestEvent $e)
    {
        $this->logger->info("PASSED");
    }

    public function testFail(FailEvent $e)
    {
        $this->logger->alert($e->getFail()->getMessage());
        $this->logger->info("# FAILED #");
    }

    public function testError(FailEvent $e)
    {
        $this->logger->alert($e->getFail()->getMessage());
        $this->logger->info("# ERROR #");
    }

    public function testSkipped(FailEvent $e)
    {
        $this->logger->info("# Skipped #");
    }

    public function testIncomplete(FailEvent $e)
    {
        $this->logger->info("# Incomplete #");
    }

    public function beforeStep(StepEvent $e)
    {
        $this->logger->info($e->getStep()->getHumanizedAction());
    }

}
