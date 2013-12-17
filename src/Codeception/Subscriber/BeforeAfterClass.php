<?php

namespace Codeception\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\Event\SuiteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeAfterClass implements EventSubscriberInterface {

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

    static function getSubscribedEvents()
    {
        return array(
            CodeceptionEvents::SUITE_BEFORE => 'setUpBeforeClass',
            CodeceptionEvents::SUITE_AFTER  => 'tearDownAfterClass'
        );
    }
}
