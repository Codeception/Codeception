<?php declare(strict_types=1);

namespace Codeception\EventDispatcher\Subscriber;

use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\PassedWithWarningSubscriber;

final class TestPassedWithWarningSubscriber extends Subscriber implements PassedWithWarningSubscriber
{
    public function notify(PassedWithWarning $event): void
    {
        $this->eventDispatcher()->testPassedWithWarning($event);
    }
}
