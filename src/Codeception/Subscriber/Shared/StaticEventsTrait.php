<?php

declare(strict_types=1);

namespace Codeception\Subscriber\Shared;

trait StaticEventsTrait
{
    public static function getSubscribedEvents(): array
    {
        return static::$events;
    }
}
