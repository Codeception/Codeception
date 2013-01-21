<?php
namespace Codeception\Command;

use \Symfony\Component\Yaml\Yaml;

class Base extends \Symfony\Component\Console\Command\Command
{

    protected function buildPath($basePath, $testName)
    {
        $testName = str_replace('/',DIRECTORY_SEPARATOR, $testName);
        $dirs = explode(DIRECTORY_SEPARATOR, $testName);
        array_pop($dirs);

        $path = $basePath;
        foreach ($dirs as $dir) {
            $path .= DIRECTORY_SEPARATOR.$dir;
            @mkdir($path);
        }
        return $path;
    }

    protected function getNamespaces($class)
    {
        $namespaces = explode('\\', $class);
        array_pop($namespaces);
        return $namespaces;
    }

    protected function getClassName($class)
    {
        $namespaces = explode('\\', $class);
        return array_pop($namespaces);
    }

    protected function completeSuffix($filename, $suffix)
    {
        if (strpos(strrev($filename), strrev($suffix)) === 0) $filename .= '.php';
        if (strpos(strrev($filename), strrev($suffix.'.php')) !== 0) $filename .= $suffix.'.php';
        if (strpos(strrev($filename), strrev('.php')) !== 0) $filename .= '.php';
        return $filename;
    }
}
