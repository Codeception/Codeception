<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FailFast implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::SUITE_BEFORE => 'stopOnFail',
    ];

    public function stopOnFail(SuiteEvent $event): void
    {
        $event->getResult()->stopOnError(true);
        $event->getResult()->stopOnFailure(true);
    }
}
