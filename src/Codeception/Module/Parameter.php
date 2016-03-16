<?php

namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;

/**
 * Class Parameter
 *
 * @copyright 2016 PB Web Media B.V.
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
