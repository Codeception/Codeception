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

    public function __construct(Di $fallback = null)
    {
        $this->fallback = $fallback;
    }

    public function get(string $className): ?object
    {
        // normalize namespace
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
        array $constructorArgs = null,
        string $injectMethodName = self::DEFAULT_INJECT_METHOD_NAME
    ): ?object {
        // normalize namespace
        $className = ltrim($className, '\\');

        // get class from container
        if (isset($this->container[$className])) {
            if ($this->container[$className] instanceof $className) {
                return $this->container[$className];
            }

            throw new InjectionException("Failed to resolve cyclic dependencies for class '{$className}'");
        }

        // get class from parent container
        if ($this->fallback && ($class = $this->fallback->get($className))) {
            return $class;
        }

        $this->container[$className] = false; // flag that object is being instantiated

        $reflectedClass = new ReflectionClass($className);
        if (!$reflectedClass->isInstantiable()) {
            return null;
        }

        $reflectedConstructor = $reflectedClass->getConstructor();
        if (is_null($reflectedConstructor)) {
            $object = new $className();
        } else {
            try {
                if (!$constructorArgs) {
                    $constructorArgs = $this->prepareArgs($reflectedConstructor);
                }
            } catch (Exception $e) {
                throw new InjectionException("Failed to create instance of '{$className}'. " . $e->getMessage());
            }
            $object = $reflectedClass->newInstanceArgs($constructorArgs);
        }

        if ($injectMethodName !== '') {
            $this->injectDependencies($object, $injectMethodName);
        }

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
        $reflectionObjectHasMethod = $reflectedObject->hasMethod($injectMethodName);
        if (!$reflectionObjectHasMethod) {
            return;
        }

        $reflectedMethod = $reflectedObject->getMethod($injectMethodName);
        try {
            $args = $this->prepareArgs($reflectedMethod, $defaults);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if ($e->getPrevious() !== null) { // injection failed because PHP code is invalid. See #3869
                $msg .= '; ' . $e->getPrevious();
            }
            throw new InjectionException(
                "Failed to inject dependencies in instance of '{$reflectedObject->name}'. {$msg}"
            );
        }

        if (!$reflectedMethod->isPublic()) {
            $reflectedMethod->setAccessible(true);
        }
        $reflectedMethod->invokeArgs($object, $args);
    }

    protected function prepareArgs(ReflectionMethod $method, array $defaults = []): array
    {
        $args = [];
        $parameters = $method->getParameters();
        foreach ($parameters as $k => $parameter) {
            $dependency = ReflectionHelper::getClassFromParameter($parameter);
            if (is_null($dependency)) {
                if ($parameter->isVariadic()) {
                    continue;
                }
                if (!$parameter->isOptional()) {
                    if (!isset($defaults[$k])) {
                        throw new InjectionException("Parameter '{$parameter->name}' must have default value.");
                    }
                    $args[] = $defaults[$k];
                    continue;
                }
                $args[] = $parameter->getDefaultValue();
            } else {
                $arg = $this->instantiate($dependency);
                if (is_null($arg)) {
                    if ($parameter->isVariadic()) {
                        continue;
                    }
                    throw new InjectionException("Failed to resolve dependency '{$dependency}'.");
                }
                $args[] = $arg;
            }
        }
        return $args;
    }
}
