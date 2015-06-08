<?php
namespace Codeception\Util;

/**
 * This class is left for BC compatibility.
 * Most of its contents moved to parent
 *
 * Class Soap
 * @package Codeception\Util
 */
class Soap extends Xml
{
    public static function request()
    {
        return new XmlBuilder();
    }

    public static function response()
    {
        return new XmlBuilder();
    }
}
