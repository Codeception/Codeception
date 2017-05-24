<?php
namespace Codeception\Lib\Interfaces;

interface DependsOnModule
{
    /**
     * Specifies a list of modules which are required for another module.
     *
     * @return mixed array with class names as keys and
     * error messages as values: [className => errorMessage]
     */
    public function _depends();
}
