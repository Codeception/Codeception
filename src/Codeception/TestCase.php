<?php

namespace Codeception;

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
        $this->dispatcher->dispatch('step.before', new \Codeception\Event\Step($this, $step));
        try {
            $result = $step->run();
        } catch (\PHPUnit_Framework_ExpectationFailedException $fail) {
            $this->dispatcher->dispatch('step.after', new \Codeception\Event\Step($this, $step));
            throw $fail;
        }
        $this->dispatcher->dispatch('step.after', new \Codeception\Event\Step($this, $step));
        return $result;
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
