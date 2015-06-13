<?php
namespace Codeception;

use Codeception\Lib\Actor\Shared\Comment;
use Codeception\Lib\Actor\Shared\Friend;

abstract class Actor
{
    use Comment;
    use Friend;

    /**
     * @var \Codeception\Scenario
     */
    protected $scenario;

    public function __construct(\Codeception\Scenario $scenario)
    {
        $this->scenario = $scenario;
    }


    public function wantToTest($text)
    {
        $this->wantTo('test ' . $text);
    }

    public function wantTo($text)
    {
        $this->scenario->setFeature(mb_strtolower($text));
    }

    public function __call($method, $arguments) {
        $class = get_class($this);
        throw new \RuntimeException("Call to undefined method $class::$method");
    }
    
    protected function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Lazy-execution given anonymous function
     * @param $callable \Closure
     * @return $this
     */
    public function execute($callable)
    {
        $this->scenario->addStep(new \Codeception\Step\Executor($callable, array()));
        $callable();
        return $this;
    }
}
