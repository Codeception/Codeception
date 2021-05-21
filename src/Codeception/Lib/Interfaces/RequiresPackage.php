<?php

namespace Codeception\Lib\Interfaces;

interface RequiresPackage
{
    /**
     * Returns list of classes and corresponding packages required for this module
     */
    public function _requires(): array;
}
