<?php
namespace Codeception\Lib\Interfaces;

interface ConflictsWithModule
{
    /**
     * Returns class name or interface of module which can conflict with current.
     * @return string
     */
    public function _conflicts();
}
