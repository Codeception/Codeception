<?php
namespace Codeception\Subscriber;

use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Suite;
use Codeception\TestInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Module implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    public static $events = [
        Events::TEST_BEFORE  => 'before',
        Events::TEST_AFTER   => 'after',
        Events::STEP_BEFORE  => 'beforeStep',
        Events::STEP_AFTER   => 'afterStep',
        Events::TEST_FAIL    => 'failed',
        Events::TEST_ERROR   => 'failed',
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::SUITE_AFTER  => 'afterSuite'
    ];

    protected $modules = [];

    public function beforeSuite(SuiteEvent $e)
    {
        $suite = $e->getSuite();
        if (!$suite instanceof Suite) {
            return;
        }
        $this->modules = $suite->getModules();
        foreach ($this->modules as $module) {
            $module->_beforeSuite($e->getSettings());
        }
    }

    public function afterSuite()
    {
        foreach ($this->modules as $module) {
            $module->_afterSuite();
        }
    }

    public function before(TestEvent $event)
    {
        if (!$event->getTest() instanceof TestInterface) {
            return;
        }

        foreach ($this->modules as $module) {
            $module->_before($event->getTest());
        }
    }

    public function after(TestEvent $e)
    {
        if (!$e->getTest() instanceof TestInterface) {
            return;
        }
        foreach ($this->modules as $module) {
            $module->_after($e->getTest());
            $module->_resetConfig();
        }
    }

    public function failed(FailEvent $e)
    {
        if (!$e->getTest() instanceof TestInterface) {
            return;
        }
        foreach ($this->modules as $module) {
            $module->_failed($e->getTest(), $e->getFail());
        }
    }

    public function beforeStep(StepEvent $e)
    {
        foreach ($this->modules as $module) {
            $module->_beforeStep($e->getStep(), $e->getTest());
        }
    }

    public function afterStep(StepEvent $e)
    {
        foreach ($this->modules as $module) {
            $module->_afterStep($e->getStep(), $e->getTest());
        }
    }
}
