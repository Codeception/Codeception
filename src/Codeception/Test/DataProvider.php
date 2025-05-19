<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\Actor;
use Codeception\Exception\InvalidTestException;
use Codeception\Exception\TestParseException;
use Codeception\Util\Annotation;
use Codeception\Util\ReflectionHelper;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

use function sprintf;

class DataProvider
{
    public static function getDataForMethod(ReflectionMethod $method, ?ReflectionClass $class = null, ?Actor $I = null): ?iterable
    {
        $testClass     = self::getTestClass($method, $class);
        $testClassName = $testClass->getName();
        $methodName    = $method->getName();
        $annotation    = Annotation::forMethod($testClassName, $methodName);

        $data        = [];
        $rawExamples = $annotation->fetchAll('example');
        if ($rawExamples !== []) {
            $convert = is_string(reset($rawExamples));
            foreach ($rawExamples as $example) {
                $data[] = $convert ? Annotation::arrayValue($example) : $example;
            }
        }

        $providers = array_merge(
            $annotation->fetchAll('dataProvider'),
            $annotation->fetchAll('dataprovider')
        );

        if ($data === [] && $providers === []) {
            return null;
        }

        foreach ($providers as $provider) {
            [$providerClass, $providerMethod] = self::parseDataProviderAnnotation(
                $provider,
                $testClassName,
                $methodName
            );

            try {
                $refMethod = new ReflectionMethod($providerClass, $providerMethod);

                if ($refMethod->isStatic()) {
                    $result = $providerClass::$providerMethod($I);
                } else {
                    $instance = new $providerClass($providerMethod);
                    $result   = $refMethod->isPublic()
                        ? $instance->$providerMethod($I)
                        : ReflectionHelper::invokePrivateMethod($instance, $providerMethod, [$I]);
                }

                if (!is_iterable($result)) {
                    throw new InvalidTestException(
                        "DataProvider '{$provider}' for {$testClassName}::{$methodName} " .
                        'must return iterable data, got ' . gettype($result)
                    );
                }

                foreach ($result as $key => $value) {
                    is_int($key) ? $data[] = $value : $data[$key] = $value;
                }
            } catch (ReflectionException $e) {
                throw new InvalidTestException(sprintf(
                    "DataProvider '%s' for %s::%s is invalid or not callable",
                    $provider,
                    $testClassName,
                    $methodName
                ), 0, $e);
            }
        }

        return $data ?: null;
    }

    /**
     * @return string[]
     * @throws TestParseException
     */
    public static function parseDataProviderAnnotation(
        string $annotation,
        string $testClassName,
        string $testMethodName,
    ): array {
        $parts = explode('::', $annotation);

        if (count($parts) === 2 && $parts[0] !== '') {
            return $parts;
        }
        if (count($parts) === 1 || $parts[0] === '') {
            return [$testClassName, ltrim($parts[1] ?? $parts[0], ':')];
        }

        throw new InvalidTestException(sprintf(
            'Data provider "%s" specified for %s::%s is invalid',
            $annotation,
            $testClassName,
            $testMethodName
        ));
    }

    private static function getTestClass(ReflectionMethod $method, ?ReflectionClass $class): ReflectionClass
    {
        $declaringClass = $method->getDeclaringClass();

        return $declaringClass->isAbstract()
        && $class instanceof ReflectionClass
        && $declaringClass->getName() !== $class->getName()
            ? $class
            : $declaringClass;
    }
}
