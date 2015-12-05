<?php
namespace Codeception\Util;

class PropertyAccess
{
    /**
     * @deprecated Use ReflectionHelper::readPrivateProperty()
     * @param object $object
     * @param string $property
     * @param string|null $class
     * @return mixed
     */
    public static function readPrivateProperty($object, $property, $class = null)
    {
        return ReflectionHelper::readPrivateProperty($object, $property, $class);
    }
}