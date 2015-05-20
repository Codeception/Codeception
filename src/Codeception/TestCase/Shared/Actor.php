<?php
namespace Codeception\TestCase\Shared;

use Codeception\Event\StepEvent;
use Codeception\Events;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Step;
use Codeception\Scenario;
use Codeception\Lib\Parser;
use Codeception\Exception\Configuration as ConfigurationException;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;


trait Actor
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;
    protected $actor;
    protected $testName;
    protected $testFile;
    protected $env;

    /**
     * @var Scenario
     */
    protected $scenario;

    /**
     * @var \Codeception\Lib\Parser
     */
    protected $parser;


    public function initConfig()
    {
        $this->scenario  = new Scenario($this);
        $this->parser    = new Parser($this->scenario);
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

    protected $trace = [];

    public function runStep(Step $step)
    {
        $this->trace[] = $step;
        $this->fire(Events::STEP_BEFORE, new StepEvent($this, $step));
        try {
            $result = $step->run();
        } catch (ConditionalAssertionFailed $f) {
            $result = $this->getTestResultObject();
            $result->addFailure(clone($this), $f, $result->time());
        } catch (\Exception $e) {
            $this->fire(Events::STEP_AFTER, new StepEvent($this, $step));
            throw $e;
        }
        $this->fire(Events::STEP_AFTER, new StepEvent($this, $step));
        return $result;
    }

    public function getFeature()
    {
        return $this->scenario->getFeature();
    }

    public function getTrace()
    {
        return $this->trace;
    }

    public function configActor($actor)
    {
        $this->actor = $actor;
        return $this;
    }

    public function configDispatcher(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    public function configFile($file)
    {
        if (!is_file($file)) {
            throw new ConfigurationException("Test file $file not found");
        }

        $this->testFile = $file;
        return $this;
    }

    public function configName($name)
    {
        $this->testName = $name;
        return $this;
    }

    public function configEnv($env)
    {
        $this->env = $env;
        return $this;
    }

    public function config($property, $value)
    {
        $this->$property = $value;
        return $this;
    }

}