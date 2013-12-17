<?php

namespace Codeception;

use Codeception\Event\StepEvent;
use Codeception\Exception\ConditionalAssertionFailed;
use Symfony\Component\EventDispatcher\Event;

abstract class TestCase extends \PHPUnit_Framework_TestCase implements \PHPUnit_Framework_SelfDescribing
{
    /**
     * @var \Codeception\Scenario
     */
    protected $scenario;

    protected $trace = array();

    protected $backupGlobalsBlacklist = array('app');

    protected $dependencies;

    protected $dispatcher;

    protected function fire($event, Event $eventType)
    {
        foreach ($this->scenario->getGroups() as $group) {
            $this->dispatcher->dispatch($event . '.' . $group, $eventType);
        }
        $this->dispatcher->dispatch($event, $eventType);
    }

    protected function handleDependencies()
    {
        if (empty($this->dependencies)) {
            return true;
        }

        $passed = $this->getTestResultObject()->passed();
        $testNames = array_map(
            function ($testname) {
                return preg_replace('~with data set (.*?)~', '', $testname);
            },
            array_keys($passed)
        );
        $testNames = array_unique($testNames);

        foreach ($this->dependencies as $dependency) {
            if (in_array($dependency, $testNames)) {
                continue;
            }
            $this->getTestResultObject()->addError(
                 $this,
                 new \PHPUnit_Framework_SkippedTestError("This test depends on '$dependency' to pass."),
                 0
            );
            return false;
        }

        return true;
    }

    public function runStep(Step $step)
    {
        $this->trace[] = $step;
        $this->fire(CodeceptionEvents::STEP_BEFORE, new StepEvent($this, $step));
        try {
            $result = $step->run();
        } catch (ConditionalAssertionFailed $f) {
            $result = $this->getTestResultObject();
            $result->addFailure(clone($this), $f, $result->time());
        } catch (\Exception $e) {
            $this->fire(CodeceptionEvents::STEP_AFTER, new StepEvent($this, $step));
            throw $e;
        }
        $this->fire(CodeceptionEvents::STEP_AFTER, new StepEvent($this, $step));
        return $result;
    }

    public function getFeature()
    {
        return null;
    }

    public function getFileName()
    {
        return get_class($this) . '::' . $this->getName(false);
    }

    /**
     * @return \Codeception\Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    public function getTrace()
    {
        return $this->trace;
    }

    public function toString()
    {
        return $this->getFeature();
    }

    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }
}
