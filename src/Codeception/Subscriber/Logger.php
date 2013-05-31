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

    protected $suite;

    public function __construct($max_files = 3) {
        $this->path = \Codeception\Configuration::logDir();
        $this->max_files = $max_files;

        // internal log
        $logHandler = new \Monolog\Handler\RotatingFileHandler($this->path.'codeception.log', $this->max_files);
        $this->logger = new \Monolog\Logger('Codeception');
        $this->logger->pushHandler($logHandler);

    }

    public function beforeSuite(\Codeception\Event\Suite $e) {
        $this->suite = $e->getSuite();

        $paths = explode(DIRECTORY_SEPARATOR, $this->path.$this->suite->getName());
        $fullPath = '';
        foreach ($paths as $path) {
            $fullPath = $fullPath . DIRECTORY_SEPARATOR . $path;

            if(!file_exists($fullPath)){
                @mkdir($fullPath);
            }
        }
        $logFile = $this->path.$this->suite->getName(). DIRECTORY_SEPARATOR . 'testlog';
        // die($logFile);
        $this->logHandler = new \Monolog\Handler\RotatingFileHandler($logFile, $this->max_files);
    }

    public function beforeTest(\Codeception\Event\Test $e) {
        $test = $e->getTest()->getFileName();
        $suite = $this->suite->getName();

        $this->logger = new \Monolog\Logger($suite . DIRECTORY_SEPARATOR . $test);
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
            'test.fail' => 'testFail',
            'test.error' => 'testError',
            'test.incomplete' => 'testIncomplete',
            'test.skipped' => 'testSkipped',
        );
    }


}
