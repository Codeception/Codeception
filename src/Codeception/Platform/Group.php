<?php
namespace Codeception\Platform;

use Codeception\Event\Test;

class Group extends Extension {

    public static $group;

    public function before(Test $e)
    {
    }

    public function after(Test $e)
    {
    }

    static function getSubscribedEvents()
    {
        $events = array();
        if (self::$group) {
            $events = array(
                'test.before.'.self::$group => 'before',
                'test.after.'.self::$group => 'after',
            );
        }
        $events = array_merge($events, self::events());
        return $events;
    }
}
