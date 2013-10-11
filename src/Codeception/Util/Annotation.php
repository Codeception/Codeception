<?php


namespace Codeception\Util;


class Annotation {

    protected static $regex = '/@%s(?:[ \t]+(?P<%s>.*?))?[ \t]*\r?$/m';
    protected static $lastReflected = null;

    public static function fetchForClass($class, $annotation)
    {
        $reflected = new \ReflectionClass($class);
        if (preg_match(sprintf(self::$regex, $annotation, $annotation), $reflected->getDocComment(), $annotations));
        if (isset($annotations[$annotation])) return $annotations[$annotation];
    }

    public static function fetchForMethod($class, $method, $annotation)
    {
        $reflected = new \ReflectionMethod($class, $method);
        if (preg_match(sprintf(self::$regex, $annotation, $annotation), $reflected->getDocComment(), $annotations));
        if (isset($annotations[$annotation])) return $annotations[$annotation];
    }

}