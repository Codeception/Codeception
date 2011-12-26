<?php
namespace Codeception\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Logger implements EventSubscriberInterface
{

    protected $logHandler;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    protected $path;
    protected $max_files;

    public function __construct($max_files = 3) {
        $this->path = \Codeception\Configuration::logDir();
        $this->max_files = $max_files;

        // internal log
        $logHandler = new \Monolog\Handler\RotatingFileHandler($this->path.'codeception.log', $this->max_files);
        $this->logger = new \Monolog\Logger('Codeception');
        $this->logger->pushHandler($logHandler);

    }

    public function beforeSuite(\Codeception\Event\Suite $e) {
        $suite = $e->getSuite()->getName();
        $this->logHandler = new \Monolog\Handler\RotatingFileHandler($this->path.$suite, $this->max_files);
    }

    public function beforeTest(\Codeception\Event\Test $e) {
        $this->logger = new \Monolog\Logger($e->getTest()->getFileName());
        $this->logger->pushHandler($this->logHandler);
    }

    public function afterTest(\Codeception\Event\Test $e) {
    }
    public function endTest(\Codeception\Event\Test $e) {
        $this->logger->info("PASSED");
    }

    public function testFail(\Codeception\Event\Fail $e) {
        $this->logger->alert($e->getFail()->getMessage());
        $this->logger->info("# FAILED #");
    }

    public function testError(\Codeception\Event\Fail $e) {
        $this->logger->alert($e->getFail()->getMessage());
        $this->logger->info("# ERROR #");
    }
    
    public function testSkipped(\Codeception\Event\Fail $e) {
        $this->logger->info("# Skipped #");
    }
    
    public function testIncomplete(\Codeception\Event\Fail $e) {
        $this->logger->info("# Incomplete #");
    }

    public function beforeStep(\Codeception\Event\Step $e) {
        $this->logger->info($e->getStep()->getHumanizedAction());
    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.before' => 'beforeSuite',
            'test.before' => 'beforeTest',
            'test.after' => 'afterTest',
            'test.end' => 'endTest',
            'step.before' => 'beforeStep',
            'fail.fail' => 'testFail',
            'fail.error' => 'testError',
            'fail.incomplete' => 'testIncomplete',
            'fail.skipped' => 'testSkipped',
        );
    }


}
