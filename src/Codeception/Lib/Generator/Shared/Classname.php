<?php
namespace Codeception\Lib\Generator\Shared;

trait Classname
{
    protected function removeSuffix($classname, $suffix)
    {
        $classname = preg_replace('~\.php$~', '', $classname);
        return preg_replace("~$suffix$~", '', $classname);
    }
}
