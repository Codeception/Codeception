<?php

declare(strict_types=1);

namespace Codeception\Extension;

use Codeception\Events;

use function is_array;

trait SuiteInitSubscriberTrait
{
    public static function getSubscribedEvents(): array
    {
        $events = property_exists(static::class, 'events') && is_array(static::$events)
            ? static::$events
            : [];

        $suiteInit = (array) ($events[Events::SUITE_INIT] ?? []);
        $suiteInit[] = 'receiveModuleContainer';
        $events[Events::SUITE_INIT] = $suiteInit;

        return $events;
    }
}
