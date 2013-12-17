<?php

namespace Codeception\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Cest implements EventSubscriberInterface
{
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

    static function getSubscribedEvents()
    {
        return array(
            CodeceptionEvents::TEST_BEFORE => 'beforeTest',
            CodeceptionEvents::TEST_AFTER  => 'afterTest',
            CodeceptionEvents::TEST_FAIL   => 'failedTest'
        );
    }
}
