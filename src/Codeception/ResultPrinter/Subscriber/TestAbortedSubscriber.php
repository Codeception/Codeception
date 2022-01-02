<?php declare(strict_types=1);

namespace Codeception\ResultPrinter\Subscriber;

use PHPUnit\Event\Test\Aborted;
use PHPUnit\Event\Test\AbortedSubscriber;


final class TestAbortedSubscriber extends Subscriber implements AbortedSubscriber
{
    public function notify(Aborted $event): void
    {
        $this->printer()->testAborted();
    }
}
