<?php
namespace Codeception\Command\Shared;

use Codeception\Util\Shared\Namespaces;

trait FileSystem
{
    use Namespaces;

    protected function buildPath($basePath, $testName)
    {
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $testName = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $testName);
        $path = $basePath . DIRECTORY_SEPARATOR . $testName;
        $path = pathinfo($path, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        if (!file_exists($path)) {
            // Second argument should be mode. Well, umask() doesn't seem to return any if not set. Config may fix this.
            mkdir($path, 0775, true); // Third parameter commands to create directories recursively
        }
        return $path;
    }

    protected function getClassName($class)
    {
        $namespaces = $this->breakParts($class);
        return array_pop($namespaces);
    }

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

    protected function completeSuffix($filename, $suffix)
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

    protected function save($filename, $contents, $force = false)
    {
        if (file_exists($filename) && !$force) {
            return false;
        }

        //Write in a temporary file in order to reduce non thread-safe disk instructions,
        //and reduce risk of `Bus Errors` when running tests in parallel.
        //@see https://github.com/Codeception/Codeception/issues/2054#event-1046859271
        $tmpFilename = $filename . '.' . uniqid() . '.tmp';
        $success = @file_put_contents($tmpFilename, $contents);
        if (false === $success) {
            //no space left on device, IO errors...
            throw new \RuntimeException(sprintf('Unable to generate temporary class %s', $tmpFilename));
        }

        $success = @rename($tmpFilename, $filename);
        if (false === $success) {
            //wait 0,5s and try 3 times before throwing an excception
            for ($i = 0; ($i < 3 && !$success); $i++) {
                usleep(500000);
                $success = @rename($tmpFilename, $filename);
                if ($success) {
                    break;
                }
            }
            if (false === $success) {
                throw new \RuntimeException(sprintf('Unable to move class from %s to %s(tried 3 times)', $tmpFilename, $filename));
            }
        }

        return true;
    }
}
