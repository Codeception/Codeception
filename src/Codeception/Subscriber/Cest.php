<?php
namespace Codeception\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Cest implements EventSubscriberInterface
{
    public function beforeTest(\Codeception\Event\Test $e) {
        if (!($e->getTest() instanceof \Codeception\TestCase\Cest)) return;
        $test = $e->getTest();
        if (method_exists($test->getTestClass(), '_before')) $test->getTestClass()->_before();
    }

    public function afterTest(\Codeception\Event\Test $e) {
        if (!($e->getTest() instanceof \Codeception\TestCase\Cest)) return;
        $test = $e->getTest();
        if (method_exists($test->getTestClass(), '_after')) $test->getTestClass()->_after();
    }

    static function getSubscribedEvents()
    {
        return array(
            'test.before' => 'beforeTest',
            'test.after' => 'afterTest',
        );
    }


}
