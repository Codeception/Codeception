<?php
namespace Codeception\Command;

use \Symfony\Component\Yaml\Yaml;

class Base extends \Symfony\Component\Console\Command\Command
{

    protected function buildPath($basePath, $testName)
    {
        $testName = str_replace('/',DIRECTORY_SEPARATOR, $testName);
        $dir = pathinfo($testName, PATHINFO_DIRNAME);


        $path = $basePath.$dir;
        if (!file_exists($path)) {
            // Second argument should be mode. Well, umask() doesn't seem to return any if not set. Config may fix this.
            mkdir($path, 0775, true); // Third parameter commands to create directories recursively
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
