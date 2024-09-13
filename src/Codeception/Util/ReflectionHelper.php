<?php

declare(strict_types=1);

namespace Codeception\Util;

use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;

use function array_filter;
use function array_keys;
use function array_map;
use function array_pop;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_null;
use function is_string;
use function json_encode;
use function method_exists;
use function range;
use function var_export;

/**
 * This class contains helper methods to help with common Reflection tasks.
 */
class ReflectionHelper
{
    /**
     * Read a private property of an object.
     *
     * @throws ReflectionException
     */
    public static function readPrivateProperty(object $object, string $property, ?string $class = null): mixed
    {
        if (is_null($class)) {
            $class = $object;
        }

        $property = new ReflectionProperty($class, $property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Set a private property of an object.
     *
     * @throws ReflectionException
     */
    public static function setPrivateProperty(object $object, string $property, $value, ?string $class = null): void
    {
        if (is_null($class)) {
            $class = $object;
        }

        $property = new ReflectionProperty($class, $property);
        $property->setAccessible(true);

        $property->setValue($object, $value);
    }

    /**
     * Invoke a private method of an object.
     *
     * @throws ReflectionException
     */
    public static function invokePrivateMethod(?object $object, string $method, array $args = [], ?string $class = null): mixed
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
        $path = explode('\\', $object::class);
        return array_pop($path);
    }

    public static function getClassFromParameter(ReflectionParameter $parameter): ?string
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
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
                $constName = (string)$parameter->getDefaultValueConstantName();
                if (str_contains($constName, '::')) {
                    [$class, $const] = explode('::', $constName);
                    if (in_array($class, ['self', 'static'])) {
                        $constName = '\\' . $parameter->getDeclaringClass()->getName() . '::' . $const;
                    } elseif (!str_starts_with($class, '\\')) {
                        $constName = '\\' . $constName;
                    }
                }

                return $constName;
            }

            return self::phpEncodeValue($parameter->getDefaultValue());
        }

        $type = $parameter->getType();
        // Default to 'null' if explicitly allowed or there is no specific type hint.
        if (!$type || $type->allowsNull() || !$type instanceof ReflectionNamedType || !$type->isBuiltin()) {
            return 'null';
        }

        // Default value should match the parameter type if 'null' is NOT allowed.
        return match ($type->getName()) {
            'string' => "''",
            'array' => '[]',
            'boolean' => 'false',
            'int', 'integer', 'float', 'double', 'number', 'numeric' => '0',
            default => 'null',
        };
    }

    public static function phpEncodeValue(mixed $value): string
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
        $isPlainArray = fn (array $value): bool => ($value === [])
            || (
                (array_keys($value) === range(0, count($value) - 1))
                && ([] === array_filter(array_keys($value), 'is_string'))
            );

        if ($isPlainArray($array)) {
            return '[' . implode(', ', array_map(fn($value): string => self::phpEncodeValue($value), $array)) . ']';
        }

        $values = array_map(
            fn ($key): string => self::phpEncodeValue($key) . ' => ' . self::phpEncodeValue($array[$key]),
            array_keys($array)
        );

        return '[' . implode(', ', $values) . ']';
    }
}
