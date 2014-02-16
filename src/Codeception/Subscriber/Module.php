<?php

namespace Codeception\Subscriber;

use Codeception\Events;
use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\SuiteManager;
use Codeception\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Module implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        Events::TEST_BEFORE  => 'before',
        Events::TEST_AFTER   => 'after',
        Events::STEP_BEFORE  => 'beforeStep',
        Events::STEP_AFTER   => 'afterStep',
        Events::TEST_FAIL    => 'failed',
        Events::TEST_ERROR   => 'failed',
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::SUITE_AFTER  => 'afterSuite'
    ];

    public function beforeSuite(SuiteEvent $e)
    {
        foreach (SuiteManager::$modules as $module) {
            $module->_beforeSuite($e->getSettings());
        }
    }

    public function afterSuite()
    {
        foreach (SuiteManager::$modules as $module) {
            $module->_afterSuite();
        }
    }

    public function before(TestEvent $event)
    {
        if (!$event->getTest() instanceof TestCase) {
            return;
        }

        foreach (SuiteManager::$modules as $module) {
            $module->_cleanup();
            $module->_resetConfig();
            $module->_before($event->getTest());
        }
    }

    public function after(TestEvent $e)
    {
        if (!$e->getTest() instanceof TestCase) {
            return;
        }
        foreach (SuiteManager::$modules as $module) {
            $module->_after($e->getTest());
        }
    }

    public function failed(FailEvent $e)
    {
        if (!$e->getTest() instanceof TestCase) {
            return;
        }
        foreach (SuiteManager::$modules as $module) {
            $module->_failed($e->getTest(), $e->getFail());
        }
    }

    public function beforeStep(StepEvent $e)
    {
        foreach (SuiteManager::$modules as $module) {
            $module->_beforeStep($e->getStep(), $e->getTest());
        }
    }

    public function afterStep(StepEvent $e)
    {
        foreach (SuiteManager::$modules as $module) {
            $module->_afterStep($e->getStep(), $e->getTest());
        }
    }

}
