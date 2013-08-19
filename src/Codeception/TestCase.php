<?php

namespace Codeception;

use Codeception\Exception\ConditionalAssertionFailed;
use Symfony\Component\EventDispatcher\Event;

abstract class TestCase extends \PHPUnit_Framework_TestCase implements \PHPUnit_Framework_SelfDescribing
{
    protected $scenario;

    protected $trace = array();

    protected $backupGlobalsBlacklist = array('app');

    public function getFeature()
    {
        return null;
    }

    public function getFileName()
    {
        return get_class($this) . '::' . $this->getName();
    }

    public function runStep(\Codeception\Step $step)
    {
        $this->trace[] = $step;
        $this->fire('step.before', new \Codeception\Event\Step($this, $step));
        try {
            $result = $step->run();
        } catch (ConditionalAssertionFailed $f) {
            $result = $this->getTestResultObject();
            $result->stopOnFailure(false);
            $result->addFailure(clone($this), $f, $result->time());
            $result->stopOnFailure(true);
        } catch (\Exception $e) {
            $this->fire('step.after', new \Codeception\Event\Step($this, $step));
            throw $e;
        }
        $this->fire('step.after', new \Codeception\Event\Step($this, $step));
        return $result;
    }

    protected function fire($event, Event $eventType)
    {
        foreach ($this->scenario->getGroups() as $group) {
            $this->dispatcher->dispatch($event.'.'.$group, $eventType);
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

    public function getTrace()
    {
        return $this->trace;
    }

    public function toString()
    {
        return $this->getFeature();
    }

}
