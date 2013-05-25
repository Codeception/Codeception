<?php
namespace Codeception\Subscriber;

use Codeception\Event\Suite;
use Codeception\TestCase;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Module implements EventSubscriberInterface {

    public function createSuite(Suite $e)
    {

    }

    public function beforeSuite(Suite $e)
    {
        foreach (\Codeception\SuiteManager::$modules as $module) {
            $module->_beforeSuite($e->getSettings());
        }
    }

    public function afterSuite()
    {
        foreach (\Codeception\SuiteManager::$modules as $module) {
            $module->_afterSuite();
        }
    }

    public function before(\Codeception\Event\Test $e) {
        if (!$e->getTest() instanceof TestCase) return;
        foreach (\Codeception\SuiteManager::$modules as $module) {
            $module->_cleanup();
            $module->_resetConfig();
            $module->_before($e->getTest());
        }
    }
    
    public function after(\Codeception\Event\Test $e) {
        if (!$e->getTest() instanceof TestCase) return;
        foreach (\Codeception\SuiteManager::$modules as $module) {
            $module->_after($e->getTest());
        }
    }

    public function failed(\Codeception\Event\Fail $e) {
        if (!$e->getTest() instanceof TestCase) return;
        foreach (\Codeception\SuiteManager::$modules as $module) {
            $module->_failed($e->getTest(), $e->getFail());
        }
    }

    public function beforeStep(\Codeception\Event\Step $e) {
        foreach (\Codeception\SuiteManager::$modules as $module) {
            $module->_beforeStep($e->getStep(), $e->getTest());
        }
    }

    public function afterStep(\Codeception\Event\Step $e) {
        foreach (\Codeception\SuiteManager::$modules as $module) {
            $module->_afterStep($e->getStep(), $e->getTest());
        }
    }

    static function getSubscribedEvents()
    {
        return array(
            'test.before' => 'before',
            'test.after' => 'after',
            'step.before' => 'beforeStep',
            'step.after' => 'afterStep',
            'test.fail' => 'failed',
            'suite.before' => 'beforeSuite',
            'suite.after' => 'afterSuite'
        );
    }
}
