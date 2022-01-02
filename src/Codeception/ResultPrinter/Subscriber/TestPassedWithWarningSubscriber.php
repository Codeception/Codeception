<?php declare(strict_types=1);

namespace Codeception\ResultPrinter\Subscriber;

use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\PassedWithWarningSubscriber;

final class TestPassedWithWarningSubscriber extends Subscriber implements PassedWithWarningSubscriber
{
    public function notify(PassedWithWarning $event): void
    {
        $this->printer()->testPassedWithWarning();
    }
}
