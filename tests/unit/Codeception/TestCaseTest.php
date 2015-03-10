<?php

class TestCaseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Codeception\TestCase
     */
    protected $testcase;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var \Codeception\Lib\ModuleContainer
     */
    protected $moduleContainer;

    public function setUp() {
        $this->dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
        $di = new \Codeception\Lib\Di();
        $this->moduleContainer = new \Codeception\Lib\ModuleContainer($di, []);
        \Codeception\Module\EmulateModuleHelper::$onlyActions = [];
        \Codeception\Module\EmulateModuleHelper::$excludeActions = [];
        $module = $this->moduleContainer->create('EmulateModuleHelper');
        $module->_initialize();


        $this->testcase = new \Codeception\TestCase\Cept;
        $this->testcase->configDispatcher($this->dispatcher)
            ->configName('mocked test')
            ->configFile(codecept_data_dir().'SimpleCept.php')
            ->configDi($di)
            ->configModules($this->moduleContainer)
            ->initConfig();
    }

    /**
     * @group core
     */
    public function testRunStepEvents()
    {
        $events = array();
        codecept_debug($this->moduleContainer->getActions());
        $this->dispatcher->addListener('step.before', function () use (&$events) { $events[] = 'step.before'; });
        $this->dispatcher->addListener('step.after', function () use (&$events) { $events[] = 'step.after'; });
        $step = new \Codeception\Step\Action('seeEquals', array(5,5));
        $this->testcase->runStep($step);
        $this->assertEquals($events, array('step.before', 'step.after'));
    }

    /**
     * @group core
     */
    public function testRunStep() {
        $assertions = &$this->moduleContainer->getModule('EmulateModuleHelper')->assertions;
        $step = new \Codeception\Step\Action('seeEquals', array(5,5));
        $this->testcase->runStep($step);
        $this->assertEquals(1, $assertions);
        $step = new \Codeception\Step\Action('seeEquals', array(5,6));
        try {
            $this->testcase->runStep($step);
        } catch (Exception $e) {
            $this->assertInstanceOf('PHPUnit_Framework_ExpectationFailedException', $e);
        }
        $this->assertEquals(1, $assertions);
    }

    /**
     * @group core
     */
    public function testRunStepAddsTrace() {
        $step = new \Codeception\Step\Action('seeEquals', array(5,5));
        $this->testcase->runStep($step);
        $this->assertContains($step, $this->testcase->getTrace());
    }


}