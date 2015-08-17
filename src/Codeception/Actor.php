<?php
namespace Codeception;

use Codeception\Lib\Actor\Shared\Comment;
use Codeception\Lib\Actor\Shared\Friend;
use Codeception\Scenario;
use Codeception\Step\Executor;

abstract class Actor
{
    use Comment;
    use Friend;

    /**
     * @var \Codeception\Scenario
     */
    protected $scenario;

    public function __construct(Scenario $scenario)
    {
        $this->scenario = $scenario;
        $this->scenario->stopIfBlocked();
    }

    /**
     * @return \Codeception\Scenario
     */
    protected function getScenario()
    {
        return $this->scenario;
    }

    public function wantToTest($text)
    {
        $this->wantTo('test ' . $text);
    }

    public function wantTo($text)
    {
        $this->scenario->setFeature(mb_strtolower($text));
    }

    public function __call($method, $arguments)
    {
        $class = get_class($this);
        throw new \RuntimeException("Call to undefined method $class::$method");
    }
    
    /**
     * Lazy-execution given anonymous function
     * @param $callable \Closure
     * @return $this
     */
    public function execute($callable)
    {
        $this->scenario->addStep(new Executor($callable, []));
        $callable();
        return $this;
    }
}
