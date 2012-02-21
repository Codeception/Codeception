<?php
namespace Codeception;

abstract class AbstractGuy
{
    public static $methods = array();

    /**
     * @var \Codeception\Scenario
     */
    protected $scenario;

    public function __construct(\Codeception\Scenario $scenario)
    {
        $this->scenario = $scenario;

        foreach (\Codeception\SuiteManager::$modules as $module) {
            $module->_cleanup();
        }
    }

    public function wantToTest($text)
    {
        return $this->wantTo('test ' . $text);
    }

    public function wantTo($text)
    {
        $this->scenario->setFeature(strtolower($text));
        $this->scenario->comment(array('I want to ' . $text));
        return $this;
    }

    public function amTesting($method)
    {
        return $this->testMethod($method);
    }

    public function amTestingMethod($method)
    {
        $this->testMethod($method);
        return $this;
    }

    public function testMethod($signature)
    {
        if (!$this->scenario->getFeature()) {
            $this->scenario->setFeature("test method $signature()");
        } else {
            $this->scenario->setFeature($this->scenario->getFeature() . " with [[$signature]]");
        }
        $this->scenario->condition(array_merge(array('testMethod', $signature)));
        return $this;
    }

    public function expectTo($prediction)
    {
        $this->scenario->comment(array('I expect to ' . $prediction));
        return $this;
    }

    public function expect($prediction)
    {
        $this->scenario->comment(array('I expect ' . $prediction));
        return $this;
    }

    public function amGoingTo($argumentation)
    {
        $this->scenario->comment(array('I am going to ' . $argumentation));
        return $this;
    }

    public function am($role) {
        $this->scenario->comment(array('As a ' . $role));
        return $this;
    }


    public function lookForwardTo($role) {
        $this->scenario->comment(array('So that I ' . $role));
        return $this;

    }

    public function __call($method, $args)
    {
        // if (!in_array($method, array_keys(TestGuy::$methods))) throw new \RuntimeException("Action $method not defined");

        if (0 === strpos($method, 'see')) {
            $this->scenario->assertion(array_merge(array($method), $args));
        } elseif (0 === strpos($method, 'am')) {
            $this->scenario->condition(array_merge(array($method), $args));
        } else {
            $this->scenario->action(array_merge(array($method), $args));
        }
        return $this;
    }

}
