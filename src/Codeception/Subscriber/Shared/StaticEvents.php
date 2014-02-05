<?php
namespace Codeception\Subscriber\Shared;

trait StaticEvents {

    static $events;

    static function getSubscribedEvents()
    {
        return static::$events;
    }

}