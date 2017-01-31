<?php

namespace Codeception\Util;

/**
 * Holds matcher and value of mocked method
 */
class ConsecutiveMap
{
    private $consecutiveMap = [];

    public function __construct(array $consecutiveMap)
    {
        $this->consecutiveMap = $consecutiveMap;
    }

    public function getMap()
    {
        return $this->consecutiveMap;
    }
}
