<?php
namespace Codeception\Util;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

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

    /**
     * Returns class name without namespace
     *
     * (does not use reflection actually)
     *
     * @param $object
     * @return mixed
     */
    public static function getClassShortName($object)
    {
        $path = explode('\\', get_class($object));
        return array_pop($path);
    }

    /**
     * Adapted from https://github.com/Behat/Behat/pull/1313
     *
     * @param ReflectionParameter $parameter
     * @return string|null
     */
    public static function getClassFromParameter(ReflectionParameter $parameter)
    {
        if (PHP_VERSION_ID < 70100) {
            $class = $parameter->getClass();
            if ($class !== null) {
                return $class->name;
            }
            return $class;
        }

        $type = $parameter->getType();
        if ($type === null) {
            return null;
        }
        $typeString = $type->getName();

        if ($typeString === 'self') {
            return $parameter->getDeclaringClass()->getName();
        } elseif ($typeString === 'parent') {
            return $parameter->getDeclaringClass()->getParentClass()->getName();
        }

        return $typeString;
    }

    /**
     * Infer default parameter from the reflection object and format it as PHP (code) string
     *
     * @param \ReflectionParameter $param
     *
     * @return string
     */
    public static function getDefaultValue(\ReflectionParameter $param)
    {
        if ($param->isDefaultValueAvailable()) {
            if (method_exists($param, 'isDefaultValueConstant') && $param->isDefaultValueConstant()) {
                $constName = $param->getDefaultValueConstantName();
                if (false !== strpos($constName, '::')) {
                    list($class, $const) = explode('::', $constName);
                    if (in_array($class, ['self', 'static'])) {
                        $constName = $param->getDeclaringClass()->getName().'::'.$const;
                    }
                }

                return $constName;
            }

            return self::phpEncodeValue($param->getDefaultValue());
        }

        return 'null';
    }

    /**
     * PHP encode value
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function phpEncodeValue($value)
    {
        if (is_array($value)) {
            return self::phpEncodeArray($value);
        }

        if (is_string($value)) {
            return json_encode($value);
        }

        return var_export($value, true);
    }

    /**
     * Recursively PHP encode an array
     *
     * @param array $array
     *
     * @return string
     */
    public static function phpEncodeArray(array $array)
    {
        $isPlainArray = function (array $value) {
            return ((count($value) === 0)
                || (
                    (array_keys($value) === range(0, count($value) - 1))
                    && (0 === count(array_filter(array_keys($value), 'is_string'))))
            );
        };

        if ($isPlainArray($array)) {
            return '[' . implode(', ', array_map([self::class, 'phpEncodeValue'], $array)) . ']';
        }

        return '[' . implode(', ', array_map(function ($key) use ($array) {
                return self::phpEncodeValue($key) . ' => ' . self::phpEncodeValue($array[$key]);
        }, array_keys($array))) . ']';
    }
}
