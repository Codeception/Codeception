<?php

namespace Codeception\Lib\Interfaces;

interface DependsOnModule
{
    /**
     * Specifies class or module which is required for current one.
     *
     * This method should return array with key as class name and value as error message
     * [className => errorMessage]
     */
    public function _depends(): array;
}
