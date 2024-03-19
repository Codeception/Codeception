<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Lib\Di;
use Codeception\Test\Cest;
use Codeception\Test\Unit;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PrepareTest implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::TEST_BEFORE => 'prepare',
    ];

    protected array $modules = [];

    public function prepare(TestEvent $event): void
    {
        $test = $event->getTest();

        $prepareMethods = $test->getMetadata()->getParam('prepare');

        if (!$prepareMethods) {
            return;
        }
        /** @var Di $di */
        $di = $test->getMetadata()->getService('di');

        foreach ($prepareMethods as $method) {
            if ($test instanceof Cest) {
                $di->injectDependencies($test->getTestInstance(), $method);
            }
            if ($test instanceof Unit) {
                $di->injectDependencies($test, $method);
            }
        }
    }
}
