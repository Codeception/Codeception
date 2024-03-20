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
        $testClass = self::getTestClass($method, $class);
        $testClassName = $testClass->getName();
        $methodName = $method->getName();

        // example annotation
        $rawExamples = array_values(
            Annotation::forMethod($testClassName, $methodName)->fetchAll('example'),
        );

        if ($rawExamples !== []) {
            $rawExample = reset($rawExamples);
            if (is_string($rawExample)) {
                $result = array_map(
                    static fn ($v): ?array => Annotation::arrayValue($v),
                    $rawExamples
                );
            } else {
                $result = $rawExamples;
            }
        } else {
            $result = [];
        }

        // dataProvider annotation
        $dataProviderAnnotations = Annotation::forMethod($testClassName, $methodName)->fetchAll('dataProvider');
        // lowercase for back compatible
        if ($dataProviderAnnotations === []) {
            $dataProviderAnnotations = Annotation::forMethod($testClassName, $methodName)->fetchAll('dataprovider');
        }

        if ($result === [] && $dataProviderAnnotations === []) {
            return null;
        }

        foreach ($dataProviderAnnotations as $dataProviderAnnotation) {
            [$dataProviderClassName, $dataProviderMethodName] = self::parseDataProviderAnnotation(
                $dataProviderAnnotation,
                $testClassName,
                $methodName,
            );

            try {
                $dataProviderMethod = new ReflectionMethod($dataProviderClassName, $dataProviderMethodName);
                if ($dataProviderMethod->isStatic()) {
                    $dataProviderResult = call_user_func([$dataProviderClassName, $dataProviderMethodName], $I);
                } else {
                    $testInstance = new $dataProviderClassName($dataProviderMethodName);

                    if ($dataProviderMethod->isPublic()) {
                        $dataProviderResult = $testInstance->$dataProviderMethodName($I);
                    } else {
                        $dataProviderResult = ReflectionHelper::invokePrivateMethod(
                            $testInstance,
                            $dataProviderMethodName,
                            [$I]
                        );
                    }
                }

                foreach ($dataProviderResult as $key => $value) {
                    if (is_int($key)) {
                        $result [] = $value;
                    } else {
                        $result[$key] = $value;
                    }
                }
            } catch (ReflectionException) {
                throw new InvalidTestException(sprintf(
                    "DataProvider '%s' for %s::%s is invalid or not callable",
                    $dataProviderAnnotation,
                    $testClassName,
                    $methodName
                ));
            }
        }

        return $result;
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
        if (count($parts) > 2) {
            throw new InvalidTestException(
                sprintf(
                    'Data provider "%s" specified for %s::%s is invalid',
                    $annotation,
                    $testClassName,
                    $testMethodName,
                )
            );
        }

        if (count($parts) === 2) {
            return $parts;
        }

        return [
            $testClassName,
            $annotation,
        ];
    }

    /**
     * Retrieves actual test class for dataProvider.
     */
    private static function getTestClass(ReflectionMethod $dataProviderMethod, ?ReflectionClass $testClass): ReflectionClass
    {
        $dataProviderDeclaringClass = $dataProviderMethod->getDeclaringClass();
        // data provider in abstract class?
        if ($dataProviderDeclaringClass->isAbstract() && $testClass instanceof ReflectionClass && $dataProviderDeclaringClass->name !== $testClass->name) {
            return $testClass;
        }
        return $dataProviderDeclaringClass;
    }
}
