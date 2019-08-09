<?php

namespace Codeception\Util;

interface PropertyAccessorStrategy
{
    /**
     * @param object $obj
     * @param string $field
     * @return mixed
     */
    public function getProperty($obj, $field);

    /**
     * @param object|null $obj
     * @param array $data
     */
    public function setProperties($obj, array $data);

    /**
     * @param string $class
     * @param array $data
     * @return object
     */
    public function createWithProperties($class, array $data);
}
