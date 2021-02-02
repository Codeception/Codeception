<?php
namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FailFast implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    /**
     * @var array<string, string>
     */
    public static $events = [
        Events::SUITE_BEFORE => 'stopOnFail',
    ];

    public function stopOnFail(SuiteEvent $event): void
    {
        $event->getResult()->stopOnError(true);
        $event->getResult()->stopOnFailure(true);
    }
}
