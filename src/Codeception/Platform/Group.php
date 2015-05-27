<?php

namespace Codeception\Platform;

use Codeception\Events;
use Codeception\Event\TestEvent;

class Group extends Extension
{
    public static $group;

    public function _before(TestEvent $e)
    {
    }

    public function _after(TestEvent $e)
    {
    }

    static function getSubscribedEvents()
    {
        $events = array();
        if (static::$group) {
            $events = array(
                Events::TEST_BEFORE . '.' . static::$group => '_before',
                Events::TEST_AFTER . '.' . static::$group  => '_after',
            );
        }
        $events = array_merge($events, static::$events);

        return $events;
    }
}
