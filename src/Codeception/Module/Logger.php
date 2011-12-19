<?php
class Logger extends Codeception\Module
{
    protected $requiredFields = array('file');
    protected $config = array('max_files' => 3);

    /**
     * @var \Monolog\Logger
     */
    protected $logger;
    /**
     * @var \Monolog\Handler\HandlerInterface
     */
    protected $logHandler;

    public function _initialize() {
        if (!dirname($this->config['file']))
            throw new \Exception("Path for logfile does not exists. Can't write logs");

        $this->logHandler = new \Monolog\Handler\RotatingFileHandler($this->config['file'], $this->config['max_files']);

    }

    public function _before(\Codeception\TestCase $test) {
        $this->logger = new \Monolog\Logger($test->getName());
        $this->logger->pushHandler($this->logHandler);
        $this->logger->info("\n");
    }

    public function _failed(\Codeception\TestCase $test, $fail) {
        $this->logger->crit($fail->getMessage());
    }
    
    public function _beforeStep(\Codeception\Step $step) {
        $this->logger->info($step->getHumanizedAction().' '.$step->getHumanizedArguments());
    }

    public function _after(\Codeception\TestCase $test) {
        $this->logger->info("SUCESS\n");

    }

}
