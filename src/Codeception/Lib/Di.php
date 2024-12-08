<?php

declare(strict_types=1);

namespace Codeception\Lib;

use Codeception\Exception\InjectionException;
use Codeception\Util\ReflectionHelper;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use Throwable;

class Di
{
    /**
     * @var string
     */
    public const DEFAULT_INJECT_METHOD_NAME = '_inject';

    /**
     * @var object[]
     */
    protected array $container = [];

    protected ?Di $fallback = null;

    public function __construct(?Di $fallback = null)
    {
        $this->fallback = $fallback;
    }

    public function get(string $className): ?object
    {
        $className = ltrim($className, '\\');
        return $this->container[$className] ?? null;
    }

    public function set(object $class): void
    {
        $this->container[$class::class] = $class;
    }

    /**
     * @param string $injectMethodName Method which will be invoked after object creation;
     *                                 Resolved dependencies will be passed to it as arguments
     * @throws InjectionException|ReflectionException
     */
    public function instantiate(
        string $className,
        ?array $constructorArgs = null,
        string $injectMethodName = self::DEFAULT_INJECT_METHOD_NAME
    ): ?object {
        $className = ltrim($className, '\\');

        if (isset($this->container[$className])) {
            if ($this->container[$className] instanceof $className) {
                return $this->container[$className];
            }
            throw new InjectionException("Failed to resolve cyclic dependencies for class '{$className}'");
        }

        if ($this->fallback instanceof Di && ($class = $this->fallback->get($className))) {
            return $class;
        }

        $this->container[$className] = false;

        try {
            $reflectedClass = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new InjectionException("Failed to create instance of '{$className}'. " . $e->getMessage());
        }

        if (!$reflectedClass->isInstantiable()) {
            return null;
        }

        $constructorArgs = $constructorArgs ?? $this->prepareArgs($reflectedClass->getConstructor());

        try {
            $object = $reflectedClass->newInstanceArgs($constructorArgs ?? []);
        } catch (ReflectionException $e) {
            throw new InjectionException("Failed to create instance of '{$className}'. " . $e->getMessage());
        }

        $this->injectDependencies($object, $injectMethodName);
        $this->container[$className] = $object;

        return $object;
    }

    /**
     * @param string $injectMethodName Method which will be invoked with resolved dependencies as its arguments
     * @throws InjectionException|ReflectionException
     */
    public function injectDependencies(object $object, string $injectMethodName = self::DEFAULT_INJECT_METHOD_NAME, array $defaults = []): void
    {
        $reflectedObject = new ReflectionObject($object);

        if ($reflectedObject->hasMethod($injectMethodName)) {
            $reflectedMethod = $reflectedObject->getMethod($injectMethodName);

            try {
                $args = $this->prepareArgs($reflectedMethod, $defaults);
            } catch (Exception $e) {
                $msg = $e->getMessage();
                if ($e->getPrevious() instanceof Throwable) {
                    $msg .= '; ' . $e->getPrevious();
                }
                throw new InjectionException(
                    "Failed to inject dependencies in instance of '{$reflectedObject->name}'. {$msg}"
                );
            }

            $reflectedMethod->setAccessible(true);
            $reflectedMethod->invokeArgs($object, $args);
        }
    }

    protected function prepareArgs(?ReflectionMethod $method = null, array $defaults = []): array
    {
        $args = [];

        if ($method !== null) {
            foreach ($method->getParameters() as $k => $parameter) {
                $dependency = ReflectionHelper::getClassFromParameter($parameter);

                if (is_null($dependency)) {
                    if ($parameter->isVariadic()) {
                        continue;
                    }

                    if (!$parameter->isOptional()) {
                        $args[] = $defaults[$k] ?? throw new InjectionException("Parameter '{$parameter->name}' must have default value.");
                    } else {
                        $args[] = $parameter->getDefaultValue();
                    }
                } else {
                    try {
                        $arg = $this->instantiate($dependency);
                    } catch (ReflectionException $e) {
                        throw new InjectionException("Failed to resolve dependency '{$dependency}'. " . $e->getMessage());
                    }

                    if (is_null($arg) && !$parameter->isVariadic()) {
                        throw new InjectionException("Failed to resolve dependency '{$dependency}'.");
                    }
                    $args[] = $arg;
                }
            }
        }

        return $args;
    }
}
