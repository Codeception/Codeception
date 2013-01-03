<?php
namespace Codeception;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;

class Configuration
{
    protected static $suites = array();
    protected static $config = null;

    protected static $logDir = null;
    protected static $dataDir = null;
    protected static $helpersDir = null;

    protected static $dir = null;

    public static function config($config = null)
    {
        if (self::$config) return self::$config;

        if ($config === null) {
            $dir = getcwd();
            $config = file_exists('codeception.yml') ? Yaml::parse('codeception.yml') : array();
        } else {
            $dir = dirname($config);
            $config = file_exists($config) ? Yaml::parse($config) : array();
        }
        $distConfig = file_exists('codeception.dist.yml') ? Yaml::parse('codeception.dist.yml') : array();
        $config = array_merge($distConfig, $config);

        if (empty($config)) throw new \Codeception\Exception\Configuration("Configuration file is invalid");
        if (!isset($config['paths'])) throw new \Codeception\Exception\Configuration('Paths are not defined');
        if (!isset($config['paths']['data'])) throw new \Codeception\Exception\Configuration('Data path is not defined');
        if (!isset($config['paths']['log'])) throw new \Codeception\Exception\Configuration('Log path is not defined');


        if (isset($config['paths']['helpers'])) {
            // Helpers
            $helpers = Finder::create()->files()->name('*Helper.php')->in($dir . DIRECTORY_SEPARATOR .$config['paths']['helpers']);
            foreach ($helpers as $helper) include_once($helper);
        }

        if (!isset($config['suites'])) {
            $suites = Finder::create()->files()->name('*.suite.yml')->in($dir . DIRECTORY_SEPARATOR .$config['paths']['tests'])->depth(0);
            $config['suites'] = array();
            foreach ($suites as $suite) {
                preg_match('~(.*?)(\.suite|\.suite\.dist)\.yml~', $suite->getFilename(), $matches);
                $config['suites'][] = $matches[1];
            }
        }

        ini_set('memory_limit', isset($config['settings']['memory_limit']) ? $config['settings']['memory_limit'] : '1024M');

        self::$suites = $config['suites'];
        self::$config = $config;
        self::$dataDir = $config['paths']['data'];
        self::$logDir = $config['paths']['log'];
        self::$helpersDir = $config['paths']['helpers'];
        self::$dir = $dir;

        return $config;
    }

    public static function dataDir()
    {
        if (!self::$dataDir) throw new \Codeception\Exception\Configuration("Path for data not specified. Please, set data path in global config");
        return self::$dir . DIRECTORY_SEPARATOR . self::$dataDir . DIRECTORY_SEPARATOR;
    }

    public static function helpersDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$helpersDir . DIRECTORY_SEPARATOR;
    }

    public static function logDir()
    {
        if (!self::$logDir) throw new \Codeception\Exception\Configuration("Path for logs not specified. Please, set log path in global config");
        $dir = realpath(self::$dir . DIRECTORY_SEPARATOR . self::$logDir) . DIRECTORY_SEPARATOR;
        if (!is_writable($dir)) @mkdir($dir);
        if (!is_writable($dir)) {
            throw new \Codeception\Exception\Configuration("Path for logs is not writable. Please, set appropriate access mode for log path.");
        }
        return $dir;
    }

    public static function projectDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR;
    }


    public static function suiteSettings($suite, $config)
    {
        if (!in_array($suite, self::$suites)) throw new \Exception("Suite $suite was not loaded");
        $globalConf = $config['settings'];
        $globalConf['coverage'] = isset($config['coverage'])
            ? $config['coverage']
            : array();

        $moduleConf = array('modules' => isset($config['modules']) ? $config['modules'] : array());
        $path = $config['paths']['tests'];

        $suiteConf = file_exists(getcwd() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.yml") ? Yaml::parse(getcwd() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "/$suite.suite.yml") : array();
        $suiteDistconf = file_exists(getcwd() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.dist.yml") ? Yaml::parse(getcwd() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "/$suite.suite.dist.yml") : array();

        $settings = self::mergeConfigs($globalConf, $moduleConf);
        $settings = self::mergeConfigs($settings, $suiteDistconf);
        $settings = self::mergeConfigs($settings, $suiteConf);

        $settings['path'] = getcwd() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $suite . DIRECTORY_SEPARATOR;

        return $settings;
    }

    public static function suites()
    {
        return self::$suites;
    }

    public static function modules($settings)
    {
        $defaults = array('modules' => array('enabled' => array(), 'config' => array()));
        if (!isset($settings['modules'])) throw new \Codeception\Exception\Configuration('No modules configured!');

        if (file_exists($guy = $settings['path'] . DIRECTORY_SEPARATOR . $settings['class_name'] . '.php')) require_once $guy;
        // if (!class_exists($settings['class_name'])) throw new \Codeception\Exception\Configuration("No guys were found. Tried to find {$settings['class_name']} but he was not there.");

        $modules = array();

        $settings = self::mergeConfigs($defaults, $settings);

        $moduleNames = $settings['modules']['enabled'];
        foreach ($moduleNames as $moduleName) {
            $classname = '\Codeception\Module\\' . $moduleName;
            $module = new $classname;
            $modules[$moduleName] = $module;

            if (isset($settings['modules']['config'][$moduleName])) {
                $module->_setConfig($settings['modules']['config'][$moduleName]);
            } else {
                if ($module->_hasRequiredFields()) throw new \Codeception\Exception\ModuleConfig($moduleName, "Module $moduleName is not configured. Please check out it's required fields");
            }
        }

        return $modules;
    }

    public static function actions($modules)
    {
        $actions = array();
        foreach ($modules as $modulename => $module) {
            $module->_initialize();
            $class = new \ReflectionClass('\Codeception\Module\\' . $modulename);
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if (strpos($method->name, '_') === 0) continue;
                $actions[$method->name] = $modulename;
            }
        }
        return $actions;
    }

    protected static function mergeConfigs($a1, $a2)
    {
        if (!is_array($a1) || !is_array($a2))
            return $a2;
        $res = array();
        foreach ($a2 as $k2 => $v2)
        {
            if (!isset($a1[$k2])) { // if no such key
                $res[$k2] = $v2;
                continue;
            }
            $res[$k2] = self::mergeConfigs($a1[$k2], $v2);
            unset($a1[$k2]);
        }
        foreach ($a1 as $k1 => $v1) // only single elements here left
            $res[$k1] = $v1;
        return $res;
    }



}
