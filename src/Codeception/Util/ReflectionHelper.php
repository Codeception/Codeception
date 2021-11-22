<?php

declare(strict_types=1);

namespace Codeception\Util;

use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use function array_filter;
use function array_keys;
use function array_map;
use function array_pop;
use function count;
use function explode;
use function get_class;
use function implode;
use function in_array;
use function is_array;
use function is_null;
use function is_string;
use function json_encode;
use function method_exists;
use function range;
use function strpos;
use function substr;
use function var_export;

/**
 * This class contains helper methods to help with common Reflection tasks.
 */
class ReflectionHelper
{
    /**
     * Read a private property of an object.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function readPrivateProperty(object $object, string $property, string $class = null)
    {
        if (is_null($class)) {
            $class = $object;
        }

        $property = new ReflectionProperty($class, $property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Invoke a private method of an object.
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function invokePrivateMethod(object $object, string $method, array $args = [], string $class = null)
    {
        if (is_null($class)) {
            $class = $object;
        }

        $method = new ReflectionMethod($class, $method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }

    /**
     * Returns class name without namespace
     *
     * (does not use reflection actually)
     */
    public static function getClassShortName(object $object): string
    {
        $path = explode('\\', get_class($object));
        return array_pop($path);
    }

    public static function getClassFromParameter(ReflectionParameter $parameter): ?string
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionType || $type->isBuiltin()) {
            return null;
        }
        $typeString = $type->getName();
        if ($typeString === 'self') {
            return $parameter->getDeclaringClass()->getName();
        }

        if ($typeString === 'parent') {
            return $parameter->getDeclaringClass()->getParentClass()->getName();
        }

        return $typeString;
    }

    /**
     * Infer default parameter from the reflection object and format it as PHP (code) string
     */
    public static function getDefaultValue(ReflectionParameter $parameter): string
    {
        if ($parameter->isDefaultValueAvailable()) {
            if (method_exists($parameter, 'isDefaultValueConstant') && $parameter->isDefaultValueConstant()) {
                $constName = $parameter->getDefaultValueConstantName();
                if (false !== strpos($constName, '::')) {
                    [$class, $const] = explode('::', $constName);
                    if (in_array($class, ['self', 'static'])) {
                        $constName = '\\' . $parameter->getDeclaringClass()->getName() . '::' . $const;
                    } elseif (substr($class, 0, 1) !== '\\') {
                        $constName = '\\' . $constName;
                    }
                }

                return $constName;
            }

            return self::phpEncodeValue($parameter->getDefaultValue());
        }

        $type = $parameter->getType();
        // Default to 'null' if explicitly allowed or there is no specific type hint.
        if (!$type || $type->allowsNull() || !$type->isBuiltin()) {
            return 'null';
        }

        // Default value should match the parameter type if 'null' is NOT allowed.
        switch ($type->getName()) {
            case 'string':
                return "''";
            case 'array':
                return '[]';
            case 'boolean':
                return 'false';
            case 'int':
            case 'integer':
            case 'float':
            case 'double':
            case 'number':
            case 'numeric':
                return '0';
            default:
                return 'null';
        }
    }

    /**
     * PHP encode value
     *
     * @param mixed $value
     */
    public static function phpEncodeValue($value): string
    {
        if (is_array($value)) {
            return self::phpEncodeArray($value);
        }

        if (is_string($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return var_export($value, true);
    }

    /**
     * Recursively PHP encode an array
     */
    public static function phpEncodeArray(array $array): string
    {
        $isPlainArray = fn(array $value): bool => ($value === [])
            || (
                (array_keys($value) === range(0, count($value) - 1))
                && ([] === array_filter(array_keys($value), 'is_string')));

        if ($isPlainArray($array)) {
            return '[' . implode(', ', array_map([self::class, 'phpEncodeValue'], $array)) . ']';
        }

        $values = array_map(
            fn($key): string => self::phpEncodeValue($key) . ' => ' . self::phpEncodeValue($array[$key]),
            array_keys($array)
        );

        return '[' . implode(', ', $values) . ']';
    }
}
