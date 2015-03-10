<?php
namespace Codeception\Lib\Generator\Shared;

trait Namespaces
{

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

    protected function breakParts($class)
    {
        $class = str_replace('/', '\\', $class);
        $namespaces = explode('\\', $class);
        if (count($namespaces)) {
            $namespaces[0] = ltrim($namespaces[0], '\\');
        }
        if (!$namespaces[0]) {
            array_shift($namespaces);
        } // remove empty namespace caused of \\
        return $namespaces;
    }


} 