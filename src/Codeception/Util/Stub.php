<?php

namespace Codeception\Util;

use Codeception\Lib\Notification;
use Codeception\Stub\Expected;

class Stub extends \Codeception\Stub
{
    public static function never($params = null)
    {
        Notification::deprecate("Stub::never is deprecated in favor of \Codeception\Stub\Expected::never");
        return Expected::never($params);
    }

    public static function once($params = null)
    {
        Notification::deprecate("Stub::once is deprecated in favor of \Codeception\Stub\Expected::once");
        return Expected::once($params);
    }

    public static function atLeastOnce($params = null)
    {
        Notification::deprecate("Stub::atLeastOnce is deprecated in favor of \Codeception\Stub\Expected::atLeastOnce");
        return Expected::atLeastOnce($params);
    }

    public static function exactly($count, $params = null)
    {
        Notification::deprecate("Stub::exactly is deprecated in favor of \Codeception\Stub\Expected::exactly");
        return Expected::exactly($count, $params);
    }
}
