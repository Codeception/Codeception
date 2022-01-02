<?php declare(strict_types=1);

namespace Codeception\EventDispatcher\Subscriber;

use PHPUnit\Event\Test\BeforeTestMethodCalled;
use PHPUnit\Event\Test\BeforeTestMethodCalledSubscriber;

final class TestBeforeTestMethodCalledSubscriber extends Subscriber implements BeforeTestMethodCalledSubscriber
{
    public function notify(BeforeTestMethodCalled $event): void
    {
        $this->eventDispatcher()->startTest($event->test());
    }
}
