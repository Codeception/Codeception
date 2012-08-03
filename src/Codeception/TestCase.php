<?php

namespace Codeception;

abstract class TestCase extends \PHPUnit_Framework_TestCase implements \PHPUnit_Framework_SelfDescribing
{
    protected $scenario;

    public function getFeature() {
        return null;
    }

    public function getFileName() {
        return get_class($this).'::'.$this->getName();
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
                $result = call_user_func_array(array($activeModule, $action), $arguments);
            } else {
                throw new \RuntimeException("Action can't be called");
            }
        } catch (\PHPUnit_Framework_ExpectationFailedException $fail) {
            $this->dispatcher->dispatch('step.after', new \Codeception\Event\Step($this, $step));
            throw $fail;
        }

        $this->dispatcher->dispatch('step.after', new \Codeception\Event\Step($this, $step));
        return $result;
    }

    public function runComment() {
        $step = $this->scenario->getCurrentStep();
        $this->trace[] = $step;
        $this->dispatcher->dispatch('comment.before', new \Codeception\Event\Step($this, $step));
        $this->dispatcher->dispatch('comment.after', new \Codeception\Event\Step($this, $step));
    }

    /**
     * @return \Codeception\Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }


}
