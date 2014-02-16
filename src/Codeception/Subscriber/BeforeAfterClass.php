<?php
namespace Codeception\Subscriber;

use Codeception\Events;
use Codeception\Event\SuiteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeAfterClass implements EventSubscriberInterface {
    use Shared\StaticEvents;

    static $events = [
        Events::SUITE_BEFORE => 'setUpBeforeClass',
        Events::SUITE_AFTER  => 'tearDownAfterClass'
    ];

    public function setUpBeforeClass(SuiteEvent $e)
    {
        $this->processClassHooks($e->getSuite()->tests(), 'setUpBeforeClass');
    }

    public function tearDownAfterClass(SuiteEvent $e)
    {
        $this->processClassHooks($e->getSuite()->tests(), 'tearDownAfterClass');
    }

    protected function processClassHooks($tests, $method)
    {
        $processedClasses = array();
        foreach ($tests as $test) {
            $class = get_class($test);
            if (in_array($class, $processedClasses)) {
                continue;
            }
            if (is_callable(array($class, $method))) {
                call_user_func(array($class, $method));
            }
        }
    }

}
