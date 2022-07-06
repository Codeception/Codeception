<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Test\Test;
use Codeception\Test\TestCaseWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function call_user_func;
use function is_callable;

class BeforeAfterTest implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string|int[]|string[]>
     */
    protected static array $events = [
        Events::SUITE_BEFORE => 'beforeClass',
        Events::SUITE_AFTER  => ['afterClass', 100]
    ];

    public function beforeClass(SuiteEvent $event): void
    {
        foreach ($event->getSuite()->getTests() as $test) {
            $this->executeMethods($test, $test->getMetadata()->getBeforeClassMethods());
        }
    }

    public function afterClass(SuiteEvent $event): void
    {
        foreach ($event->getSuite()->getTests() as $test) {
            $this->executeMethods($test, $test->getMetadata()->getAfterClassMethods());
        }
    }

    private function executeMethods(Test $test, array $methods): void
    {
        if ($test instanceof TestCaseWrapper) {
            $test = $test->getTestCase();
        }

        foreach ($methods as $method) {
            if (is_callable([$test, $method])) {
                call_user_func([$test, $method]);
            }
        }
    }
}
