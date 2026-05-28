<?php

declare(strict_types=1);

namespace Codeception\Extension;

use Codeception\Events;

use function is_array;

trait SuiteInitSubscriberTrait
{
    public static function getSubscribedEvents(): array
    {
        if (!isset(static::$events)) {
            return [Events::SUITE_INIT => 'receiveModuleContainer'];
        }
        if (isset(static::$events[Events::SUITE_INIT])) {
            if (!is_array(static::$events[Events::SUITE_INIT])) {
                static::$events[Events::SUITE_INIT] = [[static::$events[Events::SUITE_INIT]]];
            }
            static::$events[Events::SUITE_INIT][] = ['receiveModuleContainer'];
        } else {
            static::$events[Events::SUITE_INIT] = 'receiveModuleContainer';
        }
        return static::$events;
    }
}
