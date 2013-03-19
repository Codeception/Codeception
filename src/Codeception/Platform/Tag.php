<?php
namespace Codeception\Platform;

use Codeception\Event\Test;

class Tag extends Extension {

    public static $tag;

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
