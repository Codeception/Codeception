<?php

declare(strict_types=1);

namespace Codeception\Command\Shared;

use Codeception\Util\Shared\Namespaces;

use function file_exists;
use function file_put_contents;
use function mkdir;
use function pathinfo;
use function preg_replace;
use function rtrim;
use function str_replace;
use function strrev;

trait FileSystemTrait
{
    use Namespaces;

    protected function createDirectoryFor(string $basePath, string $className = ''): string
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

    protected function completeSuffix(string $filename, string $suffix): string
    {
        if (str_starts_with(strrev($filename), strrev($suffix))) {
            $filename .= '.php';
        }
        if (!str_starts_with(strrev($filename), strrev($suffix . '.php'))) {
            $filename .= $suffix . '.php';
        }
        if (!str_starts_with(strrev($filename), strrev('.php'))) {
            $filename .= '.php';
        }

        return $filename;
    }

    protected function removeSuffix(string $classname, string $suffix): string
    {
        $classname = preg_replace('#\.php$#', '', $classname);
        return preg_replace("#{$suffix}$#", '', $classname);
    }

    protected function createFile(string $filename, string $contents, bool $force = false, int $flags = 0): bool
    {
        if (file_exists($filename) && !$force) {
            return false;
        }
        file_put_contents($filename, $contents, $flags);
        return true;
    }
}
