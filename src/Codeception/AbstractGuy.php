<?php
namespace Codeception;

abstract class AbstractGuy
{
    public static $methods = array();

    /**
     * @var \Codeception\Scenario
     */
    protected $scenario;

    protected $running = false;

    public function __construct(\Codeception\Scenario $scenario)
    {
        $this->scenario = $scenario;
    }

    /**
     * Lazy-execution given anonymous function
     * @param $callable \Closure
     * @return null|void|bool|mixed
     */
    public function execute($callable)
    {
        $this->scenario->executor($callable);
        if ($this->scenario->running()) {
            $this->scenario->runStep();
            return $this;
        } else {
        }
        return $this;
    }

    public function wantToTest($text)
    {
        return $this->wantTo('test ' . $text);
    }

    public function wantTo($text)
    {
        if ($this->scenario->running()) {
            $this->scenario->runStep();
            return $this;
        }
        $this->scenario->setFeature(strtolower($text));
        return $this;
    }

    public function expectTo($prediction)
    {
        $this->scenario->comment('I expect to ' . $prediction);
        if ($this->scenario->running()) {
            $this->scenario->runStep();
            return $this;
        }
        return $this;
    }

    public function expect($prediction)
    {
        $this->scenario->comment('I expect ' . $prediction);
        if ($this->scenario->running()) {
            $this->scenario->runStep();
            return $this;
        }
        return $this;
    }

    public function amGoingTo($argumentation)
    {
        $this->scenario->comment('I am going to ' . $argumentation);
        if ($this->scenario->running()) {
            $this->scenario->runStep();
            return $this;
        }
        return $this;
    }

    public function am($role) {
        $this->scenario->comment('As a ' . $role);
        if ($this->scenario->running()) {
            $this->scenario->runStep();
            return $this;
        }
        return $this;
    }


    public function lookForwardTo($role) {
        $this->scenario->comment('So that I ' . $role);
        if ($this->scenario->running()) {
            $this->scenario->runStep();
            return $this;
        }
    }

    public function __call($method, $arguments) {
        if ($this->scenario->running()) {
            $class = get_class($this);
            throw new \RuntimeException("Call to undefined method $class::$method");
        } else {
            $this->scenario->action($method, $arguments);
        }
    }
}
