<?php

namespace Codeception\Subscriber;

use Codeception\Events;
use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Cest implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        Events::TEST_BEFORE => 'beforeTest',
        Events::TEST_AFTER  => 'afterTest',
        Events::TEST_FAIL   => 'failedTest'
    ];

    public function beforeTest(TestEvent $e)
    {
        if (! ($e->getTest() instanceof \Codeception\TestCase\Cest)) {
            return;
        }
        $test = $e->getTest();
        if (method_exists($test->getTestClass(), '_before')) {
            $test->getTestClass()->_before($e);
        }
    }

    public function afterTest(TestEvent $e)
    {
        if (! ($e->getTest() instanceof \Codeception\TestCase\Cest)) {
            return;
        }
        $test = $e->getTest();
        if (method_exists($test->getTestClass(), '_after')) {
            $test->getTestClass()->_after($e);
        }
    }

    public function failedTest(FailEvent $e)
    {
        if (! ($e->getTest() instanceof \Codeception\TestCase\Cest)) {
            return;
        }
        $test = $e->getTest();
        if (method_exists($test->getTestClass(), '_failed')) {
            $test->getTestClass()->_failed($e);
        }
    }

}
