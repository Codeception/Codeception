<?php declare(strict_types=1);

namespace Codeception\EventDispatcher\Subscriber;

use PHPUnit\Event\Test\Aborted;
use PHPUnit\Event\Test\AbortedSubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestAbortedSubscriber extends Subscriber implements AbortedSubscriber
{
    public function notify(Aborted $event): void
    {
        $this->eventDispatcher()->testAborted($event);
    }
}
