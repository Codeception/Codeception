<?php declare(strict_types=1);

namespace Codeception\ResultPrinter\Subscriber;

use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;

final class TestSkippedSubscriber extends Subscriber implements SkippedSubscriber
{
    public function notify(Skipped $event): void
    {
        $this->printer()->testSkipped();
    }
}
