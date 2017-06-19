<?php

namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\TestParseException;
use Codeception\Suite;
use Codeception\Test\Cest;
use Codeception\Util\ReflectionHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitializeTest implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_BEFORE => 'init',
    ];

    protected $modules = [];

    public function beforeSuite(SuiteEvent $e)
    {
        $suite = $e->getSuite();
        if (!$suite instanceof Suite) {
            return;
        }
        $this->modules = $suite->getModules();
    }

    public function init(TestEvent $event)
    {
        $test = $event->getTest();
        $initModules = $test->getMetadata()->getParam('init');

        if (!$initModules) {
            return;
        }

        foreach ($initModules as $initModule) {
            $initModule = explode(' ', $initModule);
            if (count($initModule) != 2) {
                throw new TestParseException($test->getMetadata()->getName(),
                    "Annotation @init should contain module name and method to be executed.\nLike @init Doctrine2 withoutTransaction");
            }
            list($moduleName, $method) = $initModule;

            foreach ($this->modules as $module) {
                if ($module->_getName() == $moduleName) {

                    /** @var $module \Codeception\Module  **/
                    if ($test instanceof Cest) {
                        ReflectionHelper::invokePrivateMethod($test->getTestClass(), $method, [$module]);
                    }
                    if ($test instanceof \PHPUnit_Framework_TestCase) {
                        ReflectionHelper::invokePrivateMethod($test, $method, [$module]);
                    }
                }
            }
        }
    }

}