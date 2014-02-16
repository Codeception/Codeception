<?php

namespace Codeception\Command;

use Codeception\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Base extends Command
{
    public function addStyles($output)
    {
        $style = new OutputFormatterStyle('white', 'green', array('bold'));
        $output->getFormatter()->setStyle('notice', $style);
    }

    protected function buildPath($basePath, $testName)
    {
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $testName = str_replace(array('/','\\'),array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $testName);
        $path = $basePath.DIRECTORY_SEPARATOR.$testName;
        $path = pathinfo($path, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR;
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

    protected function save($filename, $contents, $force = false, $flags = null)
    {
        if (file_exists($filename) && !$force) {
            return false;
        }
        file_put_contents($filename, $contents, $flags);
        return true;
    }

    protected function getSuiteConfig($suite, $conf)
    {
        $config = Configuration::config($conf);
        return Configuration::suiteSettings($suite, $config);
    }

    protected function getGlobalConfig($conf)
    {
        return Configuration::config($conf);
    }

    protected function getSuites($conf)
    {
        Configuration::config($conf);
        return Configuration::suites();
    }

    protected function introduceAutoloader($file, $suffix, $relativePath)
    {
        $line = sprintf(
            '\Codeception\Util\Autoload::registerSuffix(\'%s\', __DIR__.DIRECTORY_SEPARATOR.\'%s\');',
            $suffix,
            $relativePath
        );

        if (!file_exists($file)) {
            return $this->save($file, "<?php \n" . $line);
        }

        $contents = file_get_contents($file);
        if (preg_match('~Autoload::registerSuffix\([\'"]' . $suffix . '[\'"]~', $contents)) {
            return false;
        }
        $contents .= "\n" . $line;

        return $this->save($file, $contents, true);
    }
}
