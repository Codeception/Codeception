<?php declare(strict_types=1);

namespace Codeception\EventDispatcher\Subscriber;

use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;


final class TestErroredSubscriber extends Subscriber implements ErroredSubscriber
{
    public function notify(Errored $event): void
    {
        $this->eventDispatcher()->testErrored($event);
    }
}
