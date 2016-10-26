<?php
namespace Codeception\Lib;

use Codeception\Exception\InjectionException;

class Di
{
    const DEFAULT_INJECT_METHOD_NAME = '_inject';

    protected $container = [];

    /**
     * @var Di
     */
    protected $fallback;

    public function __construct($fallback = null)
    {
        $this->fallback = $fallback;
    }

    public function get($className)
    {
        // normalize namespace
        $className = ltrim($className, '\\');
        return isset($this->container[$className]) ? $this->container[$className] : null;
    }

    public function set($class)
    {
        $this->container[get_class($class)] = $class;
    }

    /**
     * @param string $className
     * @param array $constructorArgs
     * @param string $injectMethodName Method which will be invoked after object creation;
     *                                 Resolved dependencies will be passed to it as arguments
     * @throws InjectionException
     * @return null|object
     */
    public function instantiate(
        $className,
        $constructorArgs = null,
        $injectMethodName = self::DEFAULT_INJECT_METHOD_NAME
    ) {
        // normalize namespace
        $className = ltrim($className, '\\');
        
        // get class from container
        if (isset($this->container[$className])) {
            if ($this->container[$className] instanceof $className) {
                return $this->container[$className];
            } else {
                throw new InjectionException("Failed to resolve cyclic dependencies for class '$className'");
            }
        }

        // get class from parent container
        if ($this->fallback) {
            if ($class = $this->fallback->get($className)) {
                return $class;
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
                throw new InjectionException("Failed to create instance of '$className'. " . $e->getMessage());
            }
            $object = $reflectedClass->newInstanceArgs($constructorArgs);
        }

        if ($injectMethodName) {
            $this->injectDependencies($object, $injectMethodName);
        }

        $this->container[$className] = $object;
        return $object;
    }

    /**
     * @param $object
     * @param string $injectMethodName Method which will be invoked with resolved dependencies as its arguments
     * @throws InjectionException
     */
    public function injectDependencies($object, $injectMethodName = self::DEFAULT_INJECT_METHOD_NAME, $defaults = [])
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
            $args = $this->prepareArgs($reflectedMethod, $defaults);
        } catch (\Exception $e) {
            throw new InjectionException(
                "Failed to inject dependencies in instance of '{$reflectedObject->name}'. " . $e->getMessage()
            );
        }

        if (!$reflectedMethod->isPublic()) {
            $reflectedMethod->setAccessible(true);
        }
        $reflectedMethod->invokeArgs($object, $args);
    }

    /**
     * @param \ReflectionMethod $method
     * @param $defaults
     * @throws InjectionException
     * @return array
     */
    protected function prepareArgs(\ReflectionMethod $method, $defaults = [])
    {
        $args = [];
        $parameters = $method->getParameters();
        foreach ($parameters as $k => $parameter) {
            $dependency = $parameter->getClass();
            if (is_null($dependency)) {
                if (!$parameter->isOptional()) {
                    if (!isset($defaults[$k])) {
                        throw new InjectionException("Parameter '$parameter->name' must have default value.");
                    }
                    $args[] = $defaults[$k];
                    continue;
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
