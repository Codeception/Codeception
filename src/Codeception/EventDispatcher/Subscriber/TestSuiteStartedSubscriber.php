<?php declare(strict_types=1);

namespace Codeception\EventDispatcher\Subscriber;

use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteStartedSubscriber extends Subscriber implements StartedSubscriber
{
    public function notify(Started $event): void
    {
        $this->eventDispatcher()->testSuiteStarted($event->testSuite());
    }
}
