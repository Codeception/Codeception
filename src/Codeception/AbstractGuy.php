<?php
namespace Codeception;

use Codeception\Step\Action;

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
        $this->scenario->runStep(new \Codeception\Step\Executor($callable, array()));
        return $this;
    }

    public function wantToTest($text)
    {
        $this->wantTo('test ' . $text);
    }

    public function wantTo($text)
    {
        $this->scenario->setFeature(mb_strtolower($text));
    }

    public function expectTo($prediction)
    {
        return $this->comment('I expect to ' . $prediction);
    }

    public function expect($prediction)
    {
        return $this->comment('I expect ' . $prediction);
    }

    public function amGoingTo($argumentation)
    {
        return $this->comment('I am going to ' . $argumentation);
    }

    public function am($role) {
        return $this->comment('As a ' . $role);
    }

    public function lookForwardTo($achieveValue)
    {
        return $this->comment('So that I ' . $achieveValue);
    }


    protected function comment($description)
    {
        $this->scenario->comment($description);
        return $this;
    }

    public function __call($method, $arguments) {
        if ($this->scenario->running()) {
            $class = get_class($this);
            throw new \RuntimeException("Call to undefined method $class::$method");
        }
        $this->scenario->addStep(new Action($method, $arguments));
    }
}
