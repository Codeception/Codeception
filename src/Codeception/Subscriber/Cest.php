<?php
namespace Codeception\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Cest implements EventSubscriberInterface
{
    public function beforeTest(\Codeception\Event\Test $e) {
        if (!($e->getTest() instanceof \Codeception\TestCase\Cest)) return;
        $test = $e->getTest();
        $scenario = $e->getTest()->getScenario();
        if (method_exists($test->getTestClass(), '_before')) $test->getTestClass()->_before($e);
    }

    public function afterTest(\Codeception\Event\Test $e) {
        if (!($e->getTest() instanceof \Codeception\TestCase\Cest)) return;
        $test = $e->getTest();
        $scenario = $e->getTest()->getScenario();
        if (method_exists($test->getTestClass(), '_after')) $test->getTestClass()->_after($e);
    }

    public function failedTest(\Codeception\Event\Fail $e) {
        if (!($e->getTest() instanceof \Codeception\TestCase\Cest)) return;
        $test = $e->getTest();
        $scenario = $e->getTest()->getScenario();
        if (method_exists($test->getTestClass(), '_failed')) $test->getTestClass()->_failed($e);
    }

    static function getSubscribedEvents()
    {
        return array(
            'test.before'	=> 'beforeTest',
            'test.after'	=> 'afterTest',
            'test.fail'	=> 'failedTest'
        );
    }


}
