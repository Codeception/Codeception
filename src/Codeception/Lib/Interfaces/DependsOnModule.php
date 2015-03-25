<?php
namespace Codeception\Lib\Interfaces;

interface DependsOnModule
{
    /**
     * @return mixed
     */
    public function _depends();
} 