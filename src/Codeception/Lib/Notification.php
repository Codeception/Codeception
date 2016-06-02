<?php
namespace Codeception\Lib;

class Notification
{
    protected static $messages = [];

    public static function warning($message, $location)
    {
        self::$messages[] = 'WARNING: ' . self::formatMessage($message, $location);
    }

    public static function deprecate($message, $location = '')
    {
        self::$messages[] = 'DEPRECATION: ' . self::formatMessage($message, $location);
    }

    private static function formatMessage($message, $location = '')
    {
        if ($location) {
            return "<bold>$message</bold> <info>$location</info>";
        }
        return $message;
    }

    public static function all()
    {
        $messages = self::$messages;
        self::$messages = [];
        return $messages;
    }
}
