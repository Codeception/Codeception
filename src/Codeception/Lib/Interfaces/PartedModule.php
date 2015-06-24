<?php
namespace Codeception\Lib\Interfaces;

/**
 * Interface PartedModule
 *
 * Module implementing this interface can be loaded partly.
 * Parts can be defined by marking methods with `@part` annotations.
 * Part of modules can be loaded by specifing part (or several parts) in config:
 *
 * ```
 * modules:
 *      enabled: [MyModule]
 *      config:
 *          MyModule:
 *              part: usefulActions
 * ```
 *
 *
 * @package Codeception\Lib\Interfaces
 */
interface PartedModule 
{
    public function _parts();
}
