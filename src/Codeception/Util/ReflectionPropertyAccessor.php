<?php

declare(strict_types=1);

namespace Codeception\Util;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use function array_key_exists;
use function get_class;
use function get_parent_class;
use function gettype;
use function is_object;

class ReflectionPropertyAccessor
{
    /**
     * @return mixed
     * @throws ReflectionException
     */
    public function getProperty(object $obj, string $field)
    {
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
     * @throws ReflectionException
     */
    private function setPropertiesForClass(?object $obj, string $class, array $data): object
    {
        $reflectionClass = new ReflectionClass($class);

        if ($obj === null) {
            $constructorParameters = [];
            $constructor = $reflectionClass->getConstructor();
            if (null !== $constructor) {
                foreach ($constructor->getParameters() as $parameter) {
                    if ($parameter->isOptional()) {
                        $constructorParameters[] = $parameter->getDefaultValue();
                    } elseif (array_key_exists($parameter->getName(), $data)) {
                        $constructorParameters[] = $data[$parameter->getName()];
                    } else {
                        throw new InvalidArgumentException(
                            'Constructor parameter "'.$parameter->getName().'" missing'
                        );
                    }
                }
            }

            $obj = $reflectionClass->newInstance($constructorParameters);
        }

        foreach ($reflectionClass->getProperties() as $property) {
            if (isset($data[$property->name])) {
                $property->setAccessible(true);
                $property->setValue($obj, $data[$property->name]);
            }
        }
        return $obj;
    }

    /**
     * @throws ReflectionException
     */
    public function setProperties(?object $obj, array $data): void
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
     * @throws ReflectionException
     */
    public function createWithProperties(string $class, array $data): object
    {
        $obj = null;
        do {
            $obj = $this->setPropertiesForClass($obj, $class, $data);
            $class = get_parent_class($class);
        } while ($class);
        return $obj;
    }
}
