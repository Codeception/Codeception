<?php

declare(strict_types=1);

namespace Codeception\Command\Shared;

use Codeception\Util\Shared\Namespaces;

trait FileSystem
{
    use Namespaces;

    protected function createDirectoryFor($basePath, $className = ''): string
    {
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        if ($className) {
            $className = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $className);
            $path = $basePath . DIRECTORY_SEPARATOR . $className;
            $basePath = pathinfo($path, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        }
        if (!file_exists($basePath)) {
            // Second argument should be mode. Well, umask() doesn't seem to return any if not set. Config may fix this.
            mkdir($basePath, 0775, true); // Third parameter commands to create directories recursively
        }
        return $basePath;
    }

    protected function completeSuffix($filename, $suffix): string
    {
        if (strpos(strrev($filename), strrev($suffix)) === 0) {
            $filename .= '.php';
        }
        if (strpos(strrev($filename), strrev($suffix . '.php')) !== 0) {
            $filename .= $suffix . '.php';
        }
        if (strpos(strrev($filename), strrev('.php')) !== 0) {
            $filename .= '.php';
        }

        return $filename;
    }

    protected function removeSuffix($classname, $suffix)
    {
        $classname = preg_replace('~\.php$~', '', $classname);
        return preg_replace("~$suffix$~", '', $classname);
    }

    protected function createFile($filename, $contents, $force = false, $flags = 0): bool
    {
        if (file_exists($filename) && !$force) {
            return false;
        }
        file_put_contents($filename, $contents, $flags);
        return true;
    }
}
