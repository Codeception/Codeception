<?php

namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;

/**
 * Class Parameter
 */
class Parameter extends CodeceptionModule
{
    /**
     * @param string $key
     *
     * @return array|string
     */
    public function getParameter($key)
    {
        return $this->moduleContainer->getParameter($key);
    }
}
