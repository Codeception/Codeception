<?php

declare(strict_types=1);

namespace Codeception\Lib;

class Notification
{
    /**
     * @var string[]
     */
    protected static array $messages = [];

    public static function warning(string $message, string $location): void
    {
        self::$messages[] = 'WARNING: ' . self::formatMessage($message, $location);
    }

    public static function deprecate(string $message, string $location = ''): void
    {
        self::$messages[] = 'DEPRECATION: ' . self::formatMessage($message, $location);
    }

    private static function formatMessage(string $message, string $location = ''): string
    {
        if ($location !== '') {
            return "<bold>{$message}</bold> <info>{$location}</info>";
        }
        return $message;
    }

    /**
     * @return string[]
     */
    public static function all(): array
    {
        $messages = self::$messages;
        self::$messages = [];
        return $messages;
    }
}
