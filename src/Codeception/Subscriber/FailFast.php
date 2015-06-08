<?php
namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FailFast implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    static $events = [
        Events::SUITE_BEFORE => 'stopOnFail',
    ];

    public function stopOnFail(SuiteEvent $e)
    {
        $e->getResult()->stopOnError(true);
        $e->getResult()->stopOnFailure(true);
    }
}
