<?php
namespace Codeception\Platform;

use Codeception\Event\Test;

class Group extends Extension {

    public static $group;

    public function _before(Test $e)
    {
    }

    public function _after(Test $e)
    {
    }

    static function getSubscribedEvents()
    {
        $events = array();
        if (self::$group) {
            $events = array(
                'test.before.'.self::$group => '_before',
                'test.after.'.self::$group => '_after',
            );
        }
        $events = array_merge($events, self::$events);
        return $events;
    }
}
