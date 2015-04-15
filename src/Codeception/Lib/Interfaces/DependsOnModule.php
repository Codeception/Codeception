<?php
namespace Codeception\Lib\Interfaces;

interface DependsOnModule
{
    /**
     * Specifies class or module which is required for current one.
     *
     * THis method should return array with key as class name and value as error message
     * [className => errorMessage
     * ]
     * @return mixed
     */
    public function _depends();
} 