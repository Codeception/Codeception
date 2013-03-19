<?php
namespace Codeception;

use Codeception\Event\Test;

class Extension {

    public static $tag;

    static function events()
    {
        return array();
    }

    public function before(Test $e)
    {
    }

    public function after(Test $e)
    {
    }

    static function getSubscribedEvents()
    {
        $events = array();
        if (self::$tag) {
            $events = array(
                'test.before.'.self::$tag => 'before',
                'test.after.'.self::$tag => 'after',
            );
        }
        $events = array_merge($events, self::events());
        return $events;
    }

}
