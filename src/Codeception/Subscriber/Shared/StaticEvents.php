<?php

declare(strict_types=1);

namespace Codeception\Subscriber\Shared;

trait StaticEvents
{
    public static function getSubscribedEvents(): array
    {
        return static::$events;
    }
}
