<?php
namespace Codeception\Util;


class PropertyAccess
{
    public static function readPrivateProperty($object, $property, $class = null)
    {
        if ($class === null) {
            $class = $object;
        }
        $property = new \ReflectionProperty($class, $property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}