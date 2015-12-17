<?php
namespace Codeception\Util;

/**
 * This class contains helper methods to help with common Reflection tasks.
 */
class ReflectionHelper
{
    /**
     * Read a private property of an object.
     *
     * @param object $object
     * @param string $property
     * @param string|null $class
     * @return mixed
     */
    public static function readPrivateProperty($object, $property, $class = null)
    {
        if (is_null($class)) {
            $class = $object;
        }

        $property = new \ReflectionProperty($class, $property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Invoke a private method of an object.
     *
     * @param object $object
     * @param string $method
     * @param array $args
     * @param string|null $class
     * @return mixed
     */
    public static function invokePrivateMethod($object, $method, $args = [], $class = null)
    {
        if (is_null($class)) {
            $class = $object;
        }

        $method = new \ReflectionMethod($class, $method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}

