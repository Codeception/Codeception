<?php
namespace Codeception\Command;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use \Symfony\Component\Yaml\Yaml;

class Base extends \Symfony\Component\Console\Command\Command
{

    public function addStyles($output)
    {
        $style = new OutputFormatterStyle('white', 'green', array('bold'));
        $output->getFormatter()->setStyle('notice', $style);
    }

    protected function buildPath($basePath, $testName)
    {
        $testName = str_replace(array('/','\\'),array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $testName);
        $path = $basePath.$testName;
        $path = pathinfo($path, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR;
        if (!file_exists($path)) {
            // Second argument should be mode. Well, umask() doesn't seem to return any if not set. Config may fix this.
            mkdir($path, 0775, true); // Third parameter commands to create directories recursively
        }
        return $path;
    }

    protected function getNamespaceString($class)
    {
        $namespaces = $this->getNamespaces($class);
        $ns = "";
        if (count($namespaces)) {
            $ns = "namespace ".implode('\\',$namespaces).";\n";
        }
        return $ns;
    }

    protected function getNamespaces($class)
    {
        $namespaces = $this->breakParts($class);
        array_pop($namespaces);
        return $namespaces;
    }

    protected function getClassName($class)
    {
        $namespaces = $this->breakParts($class);
        return array_pop($namespaces);
    }

    protected function breakParts($class)
    {
        $namespaces = explode('\\', $class);
        if (count($namespaces)) $namespaces[0] = ltrim($namespaces[0],'\\');
        if (!$namespaces[0]) array_shift($namespaces); // remove empty namespace caused of \\
        return $namespaces;
    }

    protected function completeSuffix($filename, $suffix)
    {
        if (strpos(strrev($filename), strrev($suffix)) === 0) $filename .= '.php';
        if (strpos(strrev($filename), strrev($suffix.'.php')) !== 0) $filename .= $suffix.'.php';
        if (strpos(strrev($filename), strrev('.php')) !== 0) $filename .= '.php';
        return $filename;
    }

    protected function removeSuffix($classname, $suffix)
    {
        $classname = preg_replace('~\.php$~','',$classname);
        return preg_replace("~$suffix$~",'',$classname);
    }

    protected function save($filename, $contents, $force = false)
    {
        if (file_exists($filename) && !$force) return false;
        file_put_contents($filename, $contents);
        return true;
    }

    protected function getSuiteConfig($suite, $conf)
    {
        $config = \Codeception\Configuration::config($conf);
        return \Codeception\Configuration::suiteSettings($suite, $config);
    }

    protected function getSuites($conf)
    {
        \Codeception\Configuration::config($conf);
        return \Codeception\Configuration::suites();
    }
}
