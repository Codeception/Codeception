<?php
namespace Codeception\TestCase;

use Symfony\Component\EventDispatcher\EventDispatcher;

class Cept extends \Codeception\TestCase
{
    private $name;
    protected $testfile = null;
    protected $output;
    protected $debug;
    protected $features = array();
    protected $bootstrap = null;
    protected $stopped = false;
    protected $trace = array();

    protected $dispatcher;


    public function __construct(EventDispatcher $dispatcher, array $data = array(), $dataName = '')
    {
        parent::__construct('testCodecept', $data, $dataName);
        $this->dispatcher = $dispatcher;

        if (!isset($data['file'])) throw new \Exception('File with test scenario not set. Use array(file => filepath) to set a scenario');

        $this->name = $data['name'];
        $this->scenario = new \Codeception\Scenario($this);
        $this->testfile = $data['file'];
        $this->bootstrap = isset($data['bootstrap']) ? $data['bootstrap'] : null;
    }

    public function getFileName()
    {
        return $this->name;
    }


    public function getScenarioText()
    {
        $text = implode("\r\n", $this->scenario->getSteps());
        $text = str_replace(array('((', '))'), array('...', ''), $text);
        return $text = strtoupper('I want to ' . $this->scenario->getFeature()) . "\n\n" . $text;
    }

    public function getFeature() {
        return $this->scenario->getFeature();
    }

    public function toString()
    {
        return $this->scenario->getFeature() . ' (' . $this->getFileName() . ')';
    }

    public function getTrace()
    {
        return $this->trace;
    }
    
    public function testCodecept($run = true)
    {
        if (file_exists($this->bootstrap)) require $this->bootstrap;
        $scenario = $this->scenario;
        // preload and parse steps
        require $this->testfile;

        if (!$run) return;

        $this->dispatcher->dispatch('test.before', new \Codeception\Event\Test($this));
        $scenario->run();

        try {
            require $this->testfile;
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->dispatcher->dispatch('test.fail', new \Codeception\Event\Fail($this, $e));
            throw $e;
        }
        $this->dispatcher->dispatch('test.after', new \Codeception\Event\Test($this));
    }
}
