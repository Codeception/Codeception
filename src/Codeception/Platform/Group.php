<?php
namespace Codeception\Platform;

use Codeception\Event\Test;

class Group extends Extension {

    static $group;

    public function _before(Test $e)
    {
    }

    public function _after(Test $e)
    {
    }

    static function getSubscribedEvents()
    {
        $events = array();
        if (static::$group) {
            $events = array(
                'test.before.'.static::$group => '_before',
                'test.after.'.static::$group => '_after',
            );
        }
        $events = array_merge($events, static::$events);
        return $events;
    }
}
