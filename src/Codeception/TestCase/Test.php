<?php
namespace Codeception\TestCase;

use Codeception\Exception\TestRuntime;

class Test extends \Codeception\TestCase implements \PHPUnit_Framework_SelfDescribing
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher = null;
    protected $bootstrap = null;

    public function setDispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function setBootstrap($bootstrap) {
        $this->bootstrap = $bootstrap;
    }

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->scenario = new \Codeception\Scenario($this);
    }
    
    public function getFeature() {
        $text = $this->getName();
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        return strtolower($text);
    }

    /**
     * @param $module
     * @return \Codeception\Module
     * @throws \Codeception\Exception\TestRuntime
     */
    public function getModule($module)
    {
        if (isset(\Codeception\SuiteManager::$modules[$module])) {
            return \Codeception\SuiteManager::$modules[$module];
        }
        throw new TestRuntime("Module $module is not enabled for this test suite");
    }

}
