<?php
namespace Codeception\Subscriber\Shared;

trait StaticEvents
{
    static function getSubscribedEvents()
    {
        return static::$events;
    }
}