<?php

declare(strict_types=1);

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
use DateTime;
use Exception;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

class ModuleTest extends Unit
{
    /** @var Prophet */
    private $prophet;

    protected function _setUp(): void
    {
        $this->prophet = new Prophet();
        CodeceptionModuleStub::$callOrderSequence = 1;
    }

    public function testBeforeSuiteDoesNothingWhenEventSuiteHasIncorrectType(): void
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        /** @var SuiteEvent|ObjectProphecy $suiteEvent */
        $suiteEvent = $this->prophet->prophesize(SuiteEvent::class);
        $suiteEvent->getSuite()->willReturn(new DateTime());
        $suiteEvent = $suiteEvent->reveal();

        $subject = new Module();
        $subject->beforeSuite($suiteEvent);

        $this->assertEquals(0, $module1->getCallOrder());
        $this->assertEquals(0, $module2->getCallOrder());
        $this->assertEquals(0, $module3->getCallOrder());
    }

    public function testBeforeSuite(): void
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

    public function testAfterSuite(): void
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

    public function testBeforeDoesNothingWhenEventTestHasIncorrectType(): void
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        /** @var TestEvent|ObjectProphecy $testEvent */
        $testEvent = $this->prophet->prophesize(TestEvent::class);
        $testEvent->getTest()->willReturn(new DateTime());
        $testEvent = $testEvent->reveal();

        $subject = new Module(
            [
                $module1,
                $module2,
                $module3,
            ]
        );
        $subject->before($testEvent);

        $this->assertEquals(0, $module1->getCallOrder());
        $this->assertEquals(0, $module2->getCallOrder());
        $this->assertEquals(0, $module3->getCallOrder());
    }

    public function testBefore(): void
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

    public function testAfterDoesNothingWhenEventTestHasIncorrectType(): void
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        /** @var TestEvent|ObjectProphecy $testEvent */
        $testEvent = $this->prophet->prophesize(TestEvent::class);
        $testEvent->getTest()->willReturn(new DateTime());
        $testEvent = $testEvent->reveal();

        $subject = new Module(
            [
                $module1,
                $module2,
                $module3,
            ]
        );
        $subject->after($testEvent);

        $this->assertEquals(0, $module1->getCallOrder());
        $this->assertEquals(0, $module2->getCallOrder());
        $this->assertEquals(0, $module3->getCallOrder());
    }

    public function testAfter(): void
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

    public function testFailedDoesNothingWhenEventTestHasIncorrectType(): void
    {
        /** @var ModuleContainer $moduleContainer */
        $moduleContainer = $this->prophet->prophesize(ModuleContainer::class)->reveal();

        $module1 = new CodeceptionModuleStub($moduleContainer);
        $module2 = new CodeceptionModuleStub($moduleContainer);
        $module3 = new CodeceptionModuleStub($moduleContainer);

        /** @var FailEvent|ObjectProphecy $failed */
        $failed = $this->prophet->prophesize(FailEvent::class);
        $failed->getTest()->willReturn(new DateTime());
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

        $this->assertEquals(0, $module1->getCallOrder());
        $this->assertEquals(0, $module2->getCallOrder());
        $this->assertEquals(0, $module3->getCallOrder());
    }

    public function testFailed(): void
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

    public function testBeforeStep(): void
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

    public function testAfterStep(): void
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
    private $callOrder = 0;

    public function getCallOrder(): int
    {
        return $this->callOrder;
    }

    /**
     * **HOOK** executed before suite
     *
     * @param array $settings
     */
    public function _beforeSuite($settings = []): void
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_beforeSuite($settings);
    }

    /**
     * **HOOK** executed after suite
     */
    public function _afterSuite(): void
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_afterSuite();
    }

    public function _before(TestInterface $test): void
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_before($test);
    }

    /**
     * **HOOK** executed after test
     *
     * @param TestInterface $test
     */
    public function _after(TestInterface $test): void
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_after($test);
    }

    /**
     * **HOOK** executed when test fails but before `_after`
     *
     * @param TestInterface $test
     * @param Exception $fail
     */
    public function _failed(TestInterface $test, $fail): void
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_failed($test, $fail);
    }

    /**
     * **HOOK** executed before step
     *
     * @param Step $step
     */
    public function _beforeStep(Step $step): void
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_beforeStep($step);
    }

    /**
     * **HOOK** executed after step
     *
     * @param Step $step
     */
    public function _afterStep(Step $step): void
    {
        $this->callOrder = static::$callOrderSequence++;
        parent::_afterStep($step);
    }
}
