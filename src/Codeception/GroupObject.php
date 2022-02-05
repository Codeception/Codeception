<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Event\TestEvent;

abstract class GroupObject extends Extension
{
    public static $group;

    public function _before(TestEvent $event)
    {
    }

    public function _after(TestEvent $event)
    {
    }

    public static function getSubscribedEvents(): array
    {
        $inheritedEvents = parent::getSubscribedEvents();
        $events = [];
        if (static::$group) {
            $events = [
                Events::TEST_BEFORE . '.' . static::$group => '_before',
                Events::TEST_AFTER . '.' . static::$group => '_after',
            ];
        }
        return array_merge($events, $inheritedEvents);
    }
}
