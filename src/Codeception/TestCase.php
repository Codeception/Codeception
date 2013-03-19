<?php

namespace Codeception;

use Codeception\Event\Step;
use Symfony\Component\EventDispatcher\Event;

abstract class TestCase extends \PHPUnit_Framework_TestCase implements \PHPUnit_Framework_SelfDescribing
{
    protected $scenario;

    protected $trace = array();

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
        $this->fire('step.before', new Step($this, $step));
        try {
            $result = $step->run();
        } catch (\PHPUnit_Framework_ExpectationFailedException $fail) {
            $this->fire('step.after', new Step($this, $step));
            throw $fail;
        }
        $this->fire('step.after', new Step($this, $step));
        return $result;
    }

    protected function fire($event, Event $eventType)
    {
        $this->dispatcher->dispatch($event, $eventType);
        foreach ($this->scenario->getTags() as $tag) {
            $this->dispatcher->dispatch($event.'.'.$tag, $eventType);
        }

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
        $this->getFeature();
    }

}
