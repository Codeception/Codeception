<?php
namespace Codeception\TestCase\Shared;

use Codeception\Lib\Parser;
use Codeception\Scenario;
use Codeception\Step;
use Symfony\Component\EventDispatcher\Event;

trait Actor
{

    protected $actor;
    protected $testName;
    protected $testFile;
    protected $env;

    /**
     * @var \Codeception\Scenario
     */
    protected $scenario;

    /**
     * @var \Codeception\Lib\Parser
     */
    protected $parser;


    public function initConfig()
    {
        $this->scenario = new Scenario(
            $this, [
            'env'     => $this->env,
            'modules' => $this->moduleContainer->all(),
            'name'    => $this->testName
        ]
        );
        $this->parser = new Parser($this->scenario);
        return $this;
    }

    protected function fire($event, Event $eventType)
    {
        foreach ($this->scenario->getGroups() as $group) {
            $this->dispatcher->dispatch($event . '.' . $group, $eventType);
        }
        $this->dispatcher->dispatch($event, $eventType);
    }

    /**
     * @return \Codeception\Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * @return \PHPUnit_Framework_TestResult
     */
    abstract public function getTestResultObject();



    public function getFeature()
    {
        return $this->getScenario()->getFeature();
    }

}
