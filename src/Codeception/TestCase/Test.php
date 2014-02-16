<?php

namespace Codeception\TestCase;

use Codeception\Events;
use Codeception\Event\TestEvent;
use Codeception\Exception\TestRuntime;
use Codeception\Scenario;
use Codeception\SuiteManager;
use Codeception\TestCase;

class Test extends TestCase
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher = null;
    protected $bootstrap = null;

    /**
     * @var \CodeGuy
     */
    protected $codeGuy = null;

    protected $guyClass;

    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public function setGuyClass($guy)
    {
        $this->guyClass = $guy;
    }

    protected function setUp()
    {
        if ($this->bootstrap) {
            require $this->bootstrap;
        }
        $this->scenario = new Scenario($this);
        $guy            = $this->guyClass;
        if ($guy) {
            $property      = lcfirst($guy);
            $this->codeGuy = $this->$property = new $guy($this->scenario);
        }
        $this->scenario->run();
        $this->fire(Events::TEST_BEFORE, new TestEvent($this));
        $this->_before();
    }

    /**
     * @Override
     */
    protected function _before()
    {
    }

    protected function tearDown()
    {
        $this->_after();
        $this->fire(Events::TEST_AFTER, new TestEvent($this));
    }

    /**
     * @Override
     */
    protected function _after()
    {
    }

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->scenario = new Scenario($this);
    }

    public function getFeature()
    {
        $text = $this->getName();
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        return strtolower($text);
    }

    /**
     * @param $module
     *
     * @return \Codeception\Module
     * @throws \Codeception\Exception\TestRuntime
     */
    public function getModule($module)
    {
        if (SuiteManager::hasModule($module)) {
            return SuiteManager::$modules[$module];
        }

        throw new TestRuntime("Module $module is not enabled for this test suite");
    }
}
