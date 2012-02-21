<?php

namespace Codeception;

use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class TestCase extends \PHPUnit_Framework_TestCase implements \PHPUnit_Framework_SelfDescribing
{

    protected $testfile = null;
    protected $output;
    protected $debug;
    protected $features = array();
    protected $scenario;
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

    /**
     * @return \Codeception\Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    public function getScenarioText()
    {
        $text = implode("\r\n", $this->scenario->getSteps());
        $text = str_replace(array('((', '))'), array('...', ''), $text);
        return $text = strtoupper('I want to ' . $this->scenario->getFeature()) . "\n\n" . $text;
    }

    public function __call($command, $args)
    {
        if (strrpos('Test', $command) !== 0) return;
        $this->testCodecept();
    }
    
    public function setUp() {
        $this->loadScenario();
        $this->dispatcher->dispatch('test.before', new \Codeception\Event\Test($this));
    }

    abstract public function loadScenario();

    /**
     * @test
     */
    public function testCodecept()
    {
        try {
            $this->scenario->run();
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->dispatcher->dispatch('test.fail', new \Codeception\Event\Fail($this, $e));
            throw $e;
        }
    }

    public function tearDown() {
        $this->dispatcher->dispatch('test.after', new \Codeception\Event\Test($this));
    }

    public function runStep(\Codeception\Step $step)
    {
        $this->trace[] = $step;

        if ($step->getName() == 'Comment') {
            $this->dispatcher->dispatch('comment.before', new \Codeception\Event\Step($this, $step));
            $this->dispatcher->dispatch('comment.after', new \Codeception\Event\Step($this, $step));
            return;
        }

        $action = $step->getAction();
        $arguments = $step->getArguments();

        if (!isset(\Codeception\SuiteManager::$actions[$action])) {
            $this->stopped = true;
            $this->fail("Action $action not defined");
            return;
        }

        $this->dispatcher->dispatch('step.before', new \Codeception\Event\Step($this, $step));

        $activeModule = \Codeception\SuiteManager::$modules[\Codeception\SuiteManager::$actions[$action]];

        try {
            if (is_callable(array($activeModule, $action))) {
                call_user_func_array(array($activeModule, $action), $arguments);

            } else {
                throw new \RuntimeException("Action can't be called");
            }
        } catch (\PHPUnit_Framework_ExpectationFailedException $fail) {
            $this->dispatcher->dispatch('step.after', new \Codeception\Event\Step($this, $step));
            throw $fail;
        }

        $this->dispatcher->dispatch('step.after', new \Codeception\Event\Step($this, $step));
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


}
