<?php

namespace Codeception\Platform;

use Codeception\Event\TestEvent;
use Codeception\Events;

class Group extends Extension
{
    static $group;

    public function _before(TestEvent $e)
    {
    }

    public function _after(TestEvent $e)
    {
    }

    static function getSubscribedEvents()
    {
        $events = [];
        if (static::$group) {
            $events = [
                Events::TEST_BEFORE . '.' . static::$group => '_before',
                Events::TEST_AFTER . '.' . static::$group  => '_after',
            ];
        }
        $events = array_merge($events, static::$events);

        return $events;
    }
}
