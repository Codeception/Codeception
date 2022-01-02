<?php declare(strict_types=1);

namespace Codeception\EventDispatcher\Subscriber;

use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;

final class TestFailedSubscriber extends Subscriber implements FailedSubscriber
{
    public function notify(Failed $event): void
    {
        $this->eventDispatcher()->testFailed($event->test(), $event->throwable(), $event->telemetryInfo()->time());
    }
}
