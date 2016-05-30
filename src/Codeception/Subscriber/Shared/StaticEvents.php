<?php
namespace Codeception\Subscriber\Shared;

trait StaticEvents
{
    public static function getSubscribedEvents()
    {
        return static::$events;
    }
}
