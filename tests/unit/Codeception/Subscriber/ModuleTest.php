<?php

namespace Codeception\Subscriber;

use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Lib\ModuleContainer;
use Codeception\Step;
use Codeception\Suite;
use Codeception\Test\Test;
use Codeception\Test\Unit;
use Codeception\TestInterface;
use Exception;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

class ModuleTest extends Unit
{
    /** @var Prophet */
    private $prophet;

    protected function _setUp()
    {
        $this->prophet = new Prophet();
        CodeceptionModuleStub::$callOrderSequence = 1;
    }

    public function testBeforeSuite()
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        /** @var Suite|ObjectProphecy $suite */
        $suite = $this->prophet->prophesize(Suite::class);
        $suite->getModules()->willReturn(
            [
                $module1,
                $module2,
                $module3,
            ]
        );
        $suite = $suite->reveal();


        /** @var SuiteEvent|ObjectProphecy $suiteEvent */
        $suiteEvent = $this->prophet->prophesize(SuiteEvent::class);
        $suiteEvent->getSuite()->willReturn($suite);
        $suiteEvent = $suiteEvent->reveal();

        $subject = new Module();
        $subject->beforeSuite($suiteEvent);

        $this->assertEquals(1, $module1->getCallOrder());
        $this->assertEquals(2, $module2->getCallOrder());
        $this->assertEquals(3, $module3->getCallOrder());
    }

    public function testAfterSuite()
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        $subject = new Module(
            [
                $module1,
                $module2,
                $module3,
            ]
        );
        $subject->afterSuite();

        $this->assertEquals(3, $module1->getCallOrder());
        $this->assertEquals(2, $module2->getCallOrder());
        $this->assertEquals(1, $module3->getCallOrder());
    }

    public function testBefore()
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        /** @var TestEvent|ObjectProphecy $testEvent */
        $testEvent = $this->prophet->prophesize(TestEvent::class);
        $testEvent->getTest()->willReturn($this->prophet->prophesize(Test::class)->reveal());
        $testEvent = $testEvent->reveal();

        $subject = new Module(
            [
                $module1,
                $module2,
                $module3,
            ]
        );
        $subject->before($testEvent);

        $this->assertEquals(1, $module1->getCallOrder());
        $this->assertEquals(2, $module2->getCallOrder());
        $this->assertEquals(3, $module3->getCallOrder());
    }

    public function testAfter()
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        /** @var TestEvent|ObjectProphecy $testEvent */
        $testEvent = $this->prophet->prophesize(TestEvent::class);
        $testEvent->getTest()->willReturn($this->prophet->prophesize(Test::class)->reveal());
        $testEvent = $testEvent->reveal();

        $subject = new Module(
            [
                $module1,
                $module2,
                $module3,
            ]
        );
        $subject->after($testEvent);

        $this->assertEquals(3, $module1->getCallOrder());
        $this->assertEquals(2, $module2->getCallOrder());
        $this->assertEquals(1, $module3->getCallOrder());
    }

    public function testFailed()
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        /** @var FailEvent|ObjectProphecy $failed */
        $failed = $this->prophet->prophesize(FailEvent::class);
        $failed->getTest()->willReturn($this->prophet->prophesize(Test::class)->reveal());
        $failed->getFail()->willReturn($this->prophet->prophesize(Exception::class)->reveal());
        $failed = $failed->reveal();

        $subject = new Module(
            [
                $module1,
                $module2,
                $module3,
            ]
        );
        $subject->failed($failed);

        $this->assertEquals(3, $module1->getCallOrder());
        $this->assertEquals(2, $module2->getCallOrder());
        $this->assertEquals(1, $module3->getCallOrder());
    }

    public function testBeforeStep()
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        /** @var StepEvent|ObjectProphecy $stepEvent */
        $stepEvent = $this->prophet->prophesize(StepEvent::class);
        $stepEvent->getTest()->willReturn($this->prophet->prophesize(Test::class)->reveal());
        $stepEvent->getStep()->willReturn($this->prophet->prophesize(Step::class)->reveal());
        $stepEvent = $stepEvent->reveal();

        $subject = new Module(
            [
                $module1,
                $module2,
                $module3,
            ]
        );
        $subject->beforeStep($stepEvent);

        $this->assertEquals(1, $module1->getCallOrder());
        $this->assertEquals(2, $module2->getCallOrder());
        $this->assertEquals(3, $module3->getCallOrder());
    }

    public function testAfterStep()
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        /** @var StepEvent|ObjectProphecy $stepEvent */
        $stepEvent = $this->prophet->prophesize(StepEvent::class);
        $stepEvent->getTest()->willReturn($this->prophet->prophesize(Test::class)->reveal());
        $stepEvent->getStep()->willReturn($this->prophet->prophesize(Step::class)->reveal());
        $stepEvent = $stepEvent->reveal();

        $subject = new Module(
            [
                $module1,
                $module2,
                $module3,
            ]
        );
        $subject->afterStep($stepEvent);

        $this->assertEquals(3, $module1->getCallOrder());
        $this->assertEquals(2, $module2->getCallOrder());
        $this->assertEquals(1, $module3->getCallOrder());
    }
}

class CodeceptionModuleStub extends \Codeception\Module
{
    /** @var int */
    public static $callOrderSequence = 1;

    /** @var int */
    private $callOrder;

    /**
     * @return int
     */
    public function getCallOrder(): int
    {
        return $this->callOrder;
    }

    /**
     * **HOOK** executed before suite
     *
     * @param array $settings
     */
    public function _beforeSuite($settings = [])
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_beforeSuite($settings);
    }

    /**
     * **HOOK** executed after suite
     */
    public function _afterSuite()
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_afterSuite();
    }

    public function _before(TestInterface $test)
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_before($test);
    }

    /**
     * **HOOK** executed after test
     *
     * @param TestInterface $test
     */
    public function _after(TestInterface $e)
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_after($e);
    }

    /**
     * **HOOK** executed when test fails but before `_after`
     */
    public function _failed(TestInterface $test, $fail)
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_failed($test, $fail);
    }

    /**
     * **HOOK** executed before step
     *
     * @param Step $step
     */
    public function _beforeStep(Step $step)
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_beforeStep($step);
    }

    /**
     * **HOOK** executed after step
     *
     * @param Step $step
     */
    public function _afterStep(Step $step)
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_afterStep($step);
    }
}
