<?php

namespace Codeception\Subscriber;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Di;
use Codeception\Test\Cest;
use Codeception\Test\Unit;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PrepareTest implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    public static $events = [
        Events::TEST_BEFORE => 'prepare',
    ];

    protected $modules = [];

    public function prepare(TestEvent $event)
    {
        $test = $event->getTest();
        /** @var $di Di  **/
        $prepareMethods = $test->getMetadata()->getParam('prepare');

        if (!$prepareMethods) {
            return;
        }
        $di = $test->getMetadata()->getService('di');

        foreach ($prepareMethods as $method) {

            /** @var $module \Codeception\Module  **/
            if ($test instanceof Cest) {
                $di->injectDependencies($test->getTestClass(), $method);
            }
            if ($test instanceof Unit) {
                $di->injectDependencies($test, $method);
            }
        }
    }
}
