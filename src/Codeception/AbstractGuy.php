<?php
namespace Codeception;

use Codeception\Util\Friend;

abstract class AbstractGuy
{
    public static $methods = array();

    /**
     * @var \Codeception\Scenario
     */
    protected $scenario;
    protected $friends = [];


    public function __construct(\Codeception\Scenario $scenario)
    {
        $this->scenario = $scenario;
    }

    /**
     * @param $name
     * @return Friend
     */
    public function haveFriend($name)
    {
        if (!isset($this->friends[$name])) {
            $this->friends[$name] = new Friend($name, $this);
        }
        return $this->friends[$name];
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

    public function comment($description)
    {
        $this->scenario->comment($description);
        return $this;
    }

    public function __call($method, $arguments) {
        $class = get_class($this);
        throw new \RuntimeException("Call to undefined method $class::$method");
    }
}
