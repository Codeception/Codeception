<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator\Shared;

trait Classname
{
    protected function removeSuffix(string $classname, string $suffix): string
    {
        $classname = preg_replace('#\.php$#', '', $classname);
        return preg_replace("#{$suffix}$#", '', $classname);
    }
}
