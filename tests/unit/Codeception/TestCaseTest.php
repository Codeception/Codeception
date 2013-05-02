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

    public function setUp() {
        $this->dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
        $this->testcase = $this->getMockForAbstractClass('\Codeception\TestCase\Cept', array($this->dispatcher, array('name' => 'mocked test', 'file' => 'mocked file')));
        \Codeception\SuiteManager::$modules['EmulateModuleHelper']->assertions = 0;
    }

    public function testRunStepEvents() {
        $events = array();
        $this->dispatcher->addListener('step.before', function () use (&$events) { $events[] = 'step.before'; });
        $this->dispatcher->addListener('step.after', function () use (&$events) { $events[] = 'step.after'; });
        $step = new \Codeception\Step\Action('seeEquals', array(5,5));
        $this->testcase->runStep($step);
        $this->assertEquals($events, array('step.before', 'step.after'));
    }

    public function testRunFailedTestEvent() {
        $this->markTestSkipped();
        $events = array();
        $this->dispatcher->addListener('test.fail', function () use (&$events) { $events[] = 'test.fail'; });
        $this->testcase->getScenario()->assertion('seeEquals', array(5,6));
        $this->testcase->run();
        $this->assertEquals($events, array('test.fail'));
    }
    
    public function testRunStep() {
        $assertions = &\Codeception\SuiteManager::$modules['EmulateModuleHelper']->assertions;
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

    public function testRunStepAddsTrace() {
        $step = new \Codeception\Step\Action('seeEquals', array(5,5));
        $this->testcase->runStep($step);
        $this->assertContains($step, $this->testcase->getTrace());
    }
    
    public function testSetUp() {
        $this->markTestSkipped();
        $events = array();
        $this->dispatcher->addListener('test.before', function ($e) use (&$events) { $events[] = $e->getName(); });
        $this->testcase->expects($this->once())->method('loadScenario');
        $this->testcase->setUp();
        $this->assertEquals($events, array('test.before'));
    }
    
    public function testTearDown() {
        $this->markTestSkipped();
        $events = array();
        $this->dispatcher->addListener('test.after', function ($e) use (&$events) { $events[] = $e->getName(); });
        $this->testcase->tearDown();
        $this->assertEquals($events, array('test.after'));
    }

}
