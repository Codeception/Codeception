<?php
namespace Codeception;

use Codeception\Step\Action;

abstract class AbstractGuy implements \ArrayAccess
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
        $this->scenario->addStep(new \Codeception\Step\Executor($callable, array()));
        if ($this->scenario->running()) {
            $this->scenario->runStep();
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
        $this->scenario->setFeature(mb_strtolower($text));
        return $this;
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

    /**
     * In order to have this nicely looking comments.
     *
     * ``` php
     * <?php
     *
     * $I['click on a product'];
     * $I['then I select Purchase'];
     * $I['I select shipment delivery'];
     * $I['purchase a product'];
     *
     * ```
     *
     * @param mixed $offset
     * @return mixed|void
     */
    public function offsetGet($offset)
    {
        $this->comment($offset);
    }

    public function offsetSet($offset, $value)
    {
        // not needed
    }

    public function offsetExists($offset)
    {
        return false;
    }

    public function offsetUnset($offset)
    {
       // not needed
    }

    protected function comment($description)
    {
        $this->scenario->comment($description);
        if ($this->scenario->running()) {
            $this->scenario->runStep();
            return $this;
        }
    }

    public function __call($method, $arguments) {
        if ($this->scenario->running()) {
            $class = get_class($this);
            throw new \RuntimeException("Call to undefined method $class::$method");
        }
        $this->scenario->addStep(new Action($method, $arguments));
    }
}
