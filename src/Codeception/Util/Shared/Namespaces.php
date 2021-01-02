<?php
namespace Codeception\Util\Shared;

trait Namespaces
{
    protected function breakParts($class)
    {
        // removing leading slashes and dots first
        $class = str_replace('/', '\\', ltrim($class, './\\'));
        return explode('\\', $class);
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
