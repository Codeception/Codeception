<?php
namespace Codeception\Lib;

use Codeception\Exception\InjectionException;

class Di
{
    const DEFAULT_INJECT_METHOD_NAME = '_inject';

    protected $container = [];

    /**
     * @param string $className
     * @param array $constructorArgs
     * @param string $injectMethodName Method which will be invoked after object creation; resolved dependencies will be passed to it as arguments
     * @throws InjectionException
     * @internal param array $args constructor arguments
     * @return null|object
     */
    public function instantiate($className, $constructorArgs = null, $injectMethodName = self::DEFAULT_INJECT_METHOD_NAME)
    {
        if (isset($this->container[$className])) {
            if ($this->container[$className] instanceof $className) {
                return $this->container[$className];
            } else {
                throw new InjectionException("Failed to resolve cyclic dependencies for class '$className'");
            }
        }
        $this->container[$className] = false; // flag that object is being instantiated

        $reflectedClass = new \ReflectionClass($className);
        if (!$reflectedClass->isInstantiable()) {
            return null;
        }

        $reflectedConstructor = $reflectedClass->getConstructor();
        if (is_null($reflectedConstructor)) {
            $object = new $className;
        } else {
            try {
                if (!$constructorArgs) {
                    $constructorArgs = $this->prepareArgs($reflectedConstructor);
                }
            } catch (\Exception $e) {
                throw new InjectionException("Failed to create instance of '$className'. ".$e->getMessage());
            }
            $object = $reflectedClass->newInstanceArgs($constructorArgs);
        }

        $this->injectDependencies($object, $injectMethodName);

        $this->container[$className] = $object;
        return $object;
    }

    /**
     * @param $object
     * @param string $injectMethodName Method which will be invoked with resolved dependencies as its arguments
     * @throws InjectionException
     */
    public function injectDependencies($object, $injectMethodName = self::DEFAULT_INJECT_METHOD_NAME)
    {
        if (!is_object($object)) {
            return;
        }

        $reflectedObject = new \ReflectionObject($object);
        if (!$reflectedObject->hasMethod($injectMethodName)) {
            return;
        }

        $reflectedMethod = $reflectedObject->getMethod($injectMethodName);
        try {
            $args = $this->prepareArgs($reflectedMethod);
        } catch (\Exception $e) {
            throw new InjectionException("Failed to inject dependencies in instance of '{$reflectedObject->name}'. ".$e->getMessage());
        }

        if (!$reflectedMethod->isPublic()) {
            $reflectedMethod->setAccessible(true);
        }
        $reflectedMethod->invokeArgs($object, $args);
    }

    /**
     * @param \ReflectionMethod $method
     * @return array
     * @throws \Exception
     */
    protected function prepareArgs(\ReflectionMethod $method)
    {
        $args = [];
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();
            if (is_null($dependency)) {
                if (!$parameter->isOptional()) {
                    throw new InjectionException("Parameter '$parameter->name' must have default value.");
                }
                $args[] = $parameter->getDefaultValue();
            } else {
                $arg = $this->instantiate($dependency->name);
                if (is_null($arg)) {
                    throw new InjectionException("Failed to resolve dependency '{$dependency->name}'.");
                }
                $args[] = $arg;
            }
        }
        return $args;
    }
}
