<?php
namespace Codeception\Util;


class PropertyAccess
{
    public static function readPrivateProperty($object, $property)
    {
        $property = new \ReflectionProperty($object, $property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}