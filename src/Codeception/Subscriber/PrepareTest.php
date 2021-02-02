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

    /**
     * @var array<string, string>
     */
    public static $events = [
        Events::TEST_BEFORE => 'prepare',
    ];

    /**
     * @var array
     */
    protected $modules = [];

    public function prepare(TestEvent $event): void
    {
        $test = $event->getTest();
        /** @var $di Di  **/
        $prepareMethods = $test->getMetadata()->getParam('prepare');

        if (!$prepareMethods) {
            return;
        }
        $di = $test->getMetadata()->getService('di');

        foreach ($prepareMethods as $method) {

            /** @var \Codeception\Module $module **/
            if ($test instanceof Cest) {
                $di->injectDependencies($test->getTestClass(), $method);
            }
            if ($test instanceof Unit) {
                $di->injectDependencies($test, $method);
            }
        }
    }
}
