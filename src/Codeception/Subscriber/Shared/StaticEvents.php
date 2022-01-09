<?php
namespace Codeception\Subscriber\Shared;

trait StaticEvents
{
    public static function getSubscribedEvents(): array
    {
        return static::$events;
    }
}
