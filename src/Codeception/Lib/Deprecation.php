<?php 
namespace Codeception\Lib;

class Deprecation
{
    protected static $messages = [];

    public static function add($message, $location = '')
    {
        if ($location) {
            $message = "<focus>$location</focus> | " . $message;
        }
        self::$messages[] = $message;
    }

    public static function all()
    {
        $messages = self::$messages;
        self::$messages = [];
        return $messages;
    }

}