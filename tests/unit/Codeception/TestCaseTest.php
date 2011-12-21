<?php

class TestCaseTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        \Codeception\Util\Stub\Builder::loadClasses();
    }
    
    public function testRunStep()
    {
        $module = $this->getMock('\Codeception\Module\EmulateModuleHelper', array('emptyAction', '_beforeStep', '_afterStep'));

        $module->expects($this->once())->method('emptyAction');

        \Codeception\SuiteManager::addModule($module);
        \Codeception\SuiteManager::initializeModules();

        $step = Stub::make('\Codeception\Step\Action', array('action' => 'emptyAction', 'arguments' => array()));
        $test = Stub::make('\Codeception\TestCase', array('output' => new \Codeception\Output(false), 'logger' => Stub::makeEmpty('\Monolog\Logger')));

        $test->runStep($step);

        // with invalid
        $step = Stub::make('\Codeception\Step\Action', array('action' => 'undefinedAction', 'arguments' => array()));
        try {
            $test->runStep($step);
        } catch (Exception $e) {
            $this->assertContains('undefinedAction not defined', $e->getMessage());
        }
    }

    public function testTestCodecept() {
        $scenario = $this->getMock('\Codeception\Module\EmulateModuleHelper', array('run', 'getFeature'));
        $scenario->expects($this->once())->method('run');

        $test = Stub::make('\Codeception\TestCase', array('output' => Stub::makeEmpty('\Codeception\Output'), 'scenario' => $scenario, 'logger' => Stub::makeEmpty('\Monolog\Logger')));
        $test->testCodecept();
    }




}
