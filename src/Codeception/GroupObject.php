<?php
namespace Codeception;

use Codeception\Event\TestEvent;

abstract class GroupObject extends Extension
{
    public static $group;

    public function _before(TestEvent $e)
    {
    }

    public function _after(TestEvent $e)
    {
    }

    public static function getSubscribedEvents()
    {
        $inheritedEvents = parent::getSubscribedEvents();
        $events = [];
        if (static::$group) {
            $events = [
                Events::TEST_BEFORE . '.' . static::$group => '_before',
                Events::TEST_AFTER . '.' . static::$group  => '_after',
            ];
        }
        return array_merge($events, $inheritedEvents);
    }
}
