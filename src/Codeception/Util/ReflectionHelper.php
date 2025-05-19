<?php

declare(strict_types=1);

namespace Codeception\Util;

use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;

use function array_keys;
use function array_map;
use function json_encode;
use function method_exists;
use function range;
use function var_export;

class ReflectionHelper
{
    public static function readPrivateProperty(object $object, string $property, ?string $class = null): mixed
    {
        $ref = new ReflectionProperty($class ?? $object, $property);
        $ref->setAccessible(true);
        return $ref->getValue($object);
    }

    public static function setPrivateProperty(object $object, string $property, mixed $value, ?string $class = null): void
    {
        $ref = new ReflectionProperty($class ?? $object, $property);
        $ref->setAccessible(true);
        $ref->setValue($object, $value);
    }

    public static function invokePrivateMethod(?object $object, string $method, array $args = [], ?string $class = null): mixed
    {
        $ref = new ReflectionMethod($class ?? $object, $method);
        $ref->setAccessible(true);
        return $ref->invokeArgs($object, $args);
    }

    /**
     * Returns class name without namespace (does not use reflection actually).
     */
    public static function getClassShortName(object $object): string
    {
        $full = $object::class;
        $pos  = strrpos($full, '\\');
        return $pos === false ? $full : substr($full, $pos + 1);
    }

    public static function getClassFromParameter(ReflectionParameter $parameter): ?string
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        return match ($type->getName()) {
            'self'   => $parameter->getDeclaringClass()->getName(),
            'parent' => $parameter->getDeclaringClass()->getParentClass()->getName(),
            default  => $type->getName(),
        };
    }

    /**
     * Infer default parameter from the reflection object and format it as PHP (code) string
     */
    public static function getDefaultValue(ReflectionParameter $parameter): string
    {
        if ($parameter->isDefaultValueAvailable()) {
            if (method_exists($parameter, 'isDefaultValueConstant') && $parameter->isDefaultValueConstant()) {
                $name = (string) $parameter->getDefaultValueConstantName();

                if (str_contains($name, '::')) {
                    [$class, $const] = explode('::', $name, 2);

                    if (in_array($class, ['self', 'static'], true)) {
                        $name = '\\' . $parameter->getDeclaringClass()->getName() . '::' . $const;
                    } elseif (!str_starts_with($class, '\\')) {
                        $name = '\\' . $name;
                    }
                }

                return $name;
            }

            return self::phpEncodeValue($parameter->getDefaultValue());
        }

        $type = $parameter->getType();
        if (!$type || $type->allowsNull() || !$type instanceof ReflectionNamedType || !$type->isBuiltin()) {
            return 'null';
        }

        return match ($type->getName()) {
            'string' => "''",
            'array'  => '[]',
            'bool'   => 'false',
            'int',
            'float'  => '0',
            default  => 'null',
        };
    }

    public static function phpEncodeValue(mixed $value): string
    {
        return is_array($value)
            ? self::phpEncodeArray($value)
            : (is_string($value) ? json_encode($value, JSON_THROW_ON_ERROR) : var_export($value, true));
    }

    /**
     * Recursively PHP encode an array
     */
    public static function phpEncodeArray(array $array): string
    {
        $isSequential = array_keys($array) === range(0, count($array) - 1);

        if ($isSequential) {
            return '[' . implode(', ', array_map(static fn($v): string => self::phpEncodeValue($v), $array)) . ']';
        }

        $encoded = array_map(
            static fn($k): string => self::phpEncodeValue($k) . ' => ' . self::phpEncodeValue($array[$k]),
            array_keys($array)
        );

        return '[' . implode(', ', $encoded) . ']';
    }
}
