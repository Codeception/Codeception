<?php

declare(strict_types=1);

namespace Codeception\Util\Shared;

use function array_filter;
use function array_pop;
use function array_shift;
use function count;
use function explode;
use function implode;
use function ltrim;
use function str_replace;

trait Namespaces
{
    protected function breakParts($class)
    {
        $class      = str_replace('/', '\\', $class);
        $namespaces = explode('\\', $class);
        if (count($namespaces)) {
            $namespaces[0] = ltrim($namespaces[0], '\\');
        }
        if (!$namespaces[0]) {
            array_shift($namespaces);
        } // remove empty namespace caused of \\
        return $namespaces;
    }

    protected function getShortClassName($class)
    {
        $namespaces = $this->breakParts($class);
        return array_pop($namespaces);
    }

    protected function getNamespaceString($class)
    {
        $namespaces = $this->getNamespaces($class);
        return implode('\\', $namespaces);
    }

    protected function getNamespaceHeader($class)
    {
        $str = $this->getNamespaceString($class);
        if (!$str) {
            return "";
        }
        return "namespace $str;\n";
    }

    protected function getNamespaces($class)
    {
        $namespaces = $this->breakParts($class);
        array_pop($namespaces);
        $namespaces = array_filter($namespaces, 'strlen');
        return $namespaces;
    }
}
