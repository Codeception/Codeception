<?php

use Codeception\Util\PropertyAccessorStrategy;

class CustomPropertyAccessorStrategy implements PropertyAccessorStrategy
{
    public function getProperty($obj, $field)
    {
        return $obj->getCustomProperty($field);
    }

    public function setProperties($obj, array $data)
    {
        foreach ($data as $field => $value) {
            $obj->setCustomProperty($field, $value);
        }
    }

    /**
     * @param string $class
     * @param array $data
     * @return object
     */
    public function createWithProperties($class, array $data)
    {
        $obj = new $class;
        $this->setProperties($obj, $data);
        return $obj;
    }
}
