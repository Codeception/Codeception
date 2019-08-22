<?php

namespace Codeception\Util;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use function get_class;
use function get_parent_class;
use function gettype;
use function is_object;

class ReflectionPropertyAccessor
{
    /**
     * @param object $obj
     * @param string $field
     * @return mixed
     * @throws ReflectionException
     */
    public function getProperty($obj, $field)
    {
        if (!$obj || !is_object($obj)) {
            throw new InvalidArgumentException('Cannot get property "' . $field . '" of "' . gettype($obj) . '", expecting object');
        }
        $class = get_class($obj);
        do {
            $reflectedEntity = new ReflectionClass($class);
            if ($reflectedEntity->hasProperty($field)) {
                $property = $reflectedEntity->getProperty($field);
                $property->setAccessible(true);
                return $property->getValue($obj);
            }
            $class = get_parent_class($class);
        } while ($class);
        throw new InvalidArgumentException('Property "' . $field . '" does not exists in class "' . get_class($obj) . '" and its parents');
    }

    /**
     * @param object|null $obj
     * @param string $class
     * @param array $data
     * @return object|null
     * @throws ReflectionException
     */
    private function setPropertiesForClass($obj, $class, array $data)
    {
        $reflectedEntity = new ReflectionClass($class);

        /*
         * In case we need to create the entity via reflection, we need to have all constructor parameters
         * in the $data array, in case we only want to enhance an existing instance, the given $obj has
         * already been constructed with all of its parameters outside of this function,
         * so we can skip throwing this exception.
         */
        if ($this->isObjectOf($obj, $class)) {
            $obj = $this->createObjectViaReflection($reflectedEntity, $data);
        }

        foreach ($reflectedEntity->getProperties() as $property) {
            if (isset($data[$property->name])) {
                $property->setAccessible(true);
                $property->setValue($obj, $data[$property->name]);
            }
        }

        return $obj;
    }

    /**
     * @param object|null $obj
     * @param array $data
     * @throws ReflectionException
     */
    public function setProperties($obj, array $data)
    {
        if (!$obj || !is_object($obj)) {
            throw new InvalidArgumentException('Cannot set properties for "' . gettype($obj) . '", expecting object');
        }
        $class = get_class($obj);
        do {
            $obj = $this->setPropertiesForClass($obj, $class, $data);
            $class = get_parent_class($class);
        } while ($class);
    }

    /**
     * @param string $class
     * @param array $data
     * @return object
     * @throws ReflectionException
     */
    public function createWithProperties($class, array $data)
    {
        $obj = null;
        do {
            $obj = $this->setPropertiesForClass($obj, $class, $data);
            $class = get_parent_class($class);
        } while ($class);
        return $obj;
    }

    /**
     * @param mixed $obj
     * @param string $class
     *
     * @return bool
     */
    private function isObjectOf($obj, $class)
    {
        return false === \is_a($obj, $class);
    }

    /**
     * @param array            $data
     * @param \ReflectionClass $reflectedEntity
     *
     * @return mixed
     * @throws \ReflectionException
     */
    private function createObjectViaReflection(ReflectionClass $reflectedEntity, array $data)
    {
        $constructorParameters = [];
        $constructor           = $reflectedEntity->getConstructor();
        if (null !== $constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                if ($parameter->isOptional()) {
                    $constructorParameters[] = $parameter->getDefaultValue();
                } elseif (\array_key_exists($parameter->getName(), $data)) {
                    $constructorParameters[] = $data[$parameter->getName()];
                } else {
                    throw new InvalidArgumentException(
                      'Constructor parameter "'.$parameter->getName().'" missing'
                    );
                }
            }
        }

        return $reflectedEntity->newInstance(...$constructorParameters);
    }
}
