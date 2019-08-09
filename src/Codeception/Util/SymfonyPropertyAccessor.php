<?php

namespace Codeception\Util;

use ReflectionClass;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use function class_exists;

class SymfonyPropertyAccessor implements PropertyAccessorStrategy
{
    /**
     * @var PropertyAccessor
     */
    private $pa;

    /**
     */
    public function __construct()
    {
        if (!class_exists(PropertyAccessor::class)) {
            throw new RuntimeException(
                sprintf(
                    '"%s" requires "%s" from "%s" to be installed',
                    __CLASS__,
                    PropertyAccessor::class,
                    'symfony/property-access'
                )
            );
        }

        $this->pa = new PropertyAccessor();
    }

    /**
     * @param object $obj
     * @param string $field
     * @return mixed
     */
    public function getProperty($obj, $field)
    {
        return $this->pa->getValue($obj, $field);
    }

    /**
     * @param object|null $obj
     * @param array $data
     */
    public function setProperties($obj, array $data)
    {
        foreach ($data as $field => $value) {
            $this->pa->setValue($obj, $field, $value);
        }
    }

    /**
     * @param string $class
     * @param array $data
     * @return object
     */
    public function createWithProperties($class, array $data)
    {
        $reflectedEntity = new ReflectionClass($class);
        $obj = $reflectedEntity->newInstance();
        $this->setProperties($obj, $data);
        return $obj;
    }
}
