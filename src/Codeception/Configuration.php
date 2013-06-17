<?php
namespace Codeception;

use Codeception\Exception\Configuration as ConfigurationException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;

class Configuration
{
    protected static $suites = array();
    protected static $config = null;

    protected static $dir = null;
    protected static $logDir = null;
    protected static $dataDir = null;
    protected static $helpersDir = null;
    protected static $testsDir = null;

    public static $defaultConfig = array(
        'namespace' => '',
        'include' => array(),
        'paths' => array(),
        'modules' => array(),
        'settings' => array(
            'colors' => false,
            'log' => false
        )
    );

    public static $defaultSuiteSettings = array(
        'class_name' => 'NoGuy',
        'modules' => array(
            'enabled' => array(),
            'config' => array(),
        ),
        'bootstrap' => '_bootstrap.php',
        'suite_class' => '\PHPUnit_Framework_TestSuite',
        'error_level' => 'E_ALL & ~E_STRICT & ~E_DEPRECATED',
    );

    public static function config($config_file = null)
    {
        if (!$config_file && self::$config) return self::$config;

        if ($config_file === null) $config_file = getcwd() . DIRECTORY_SEPARATOR . 'codeception.yml';
        if (is_dir($config_file)) $config_file = $config_file . DIRECTORY_SEPARATOR . 'codeception.yml';
        $dir = dirname($config_file);

        $config = self::loadConfigFile($dir . DIRECTORY_SEPARATOR . 'codeception.dist.yml', self::$defaultConfig);
        $config = self::loadConfigFile($config_file, $config);

        if ($config == self::$defaultConfig) throw new ConfigurationException("Configuration file is invalid");

        self::$dir = $dir;
        self::$config = $config;

        if (!isset($config['paths']['log'])) throw new ConfigurationException('Log path is not defined by key "paths: log"');
        self::$logDir = $config['paths']['log'];

        // config without tests, for inclusion of other configs
        if (count($config['include']) and !isset($config['paths']['tests'])) return $config;

        if (!isset($config['paths']['tests'])) throw new ConfigurationException('Tests directory is not defined in Codeception config by key "paths: tests:"');
        if (!isset($config['paths']['data'])) throw new ConfigurationException('Data path is not defined Codeception config by key "paths: data"');
        if (!isset($config['paths']['helpers'])) throw new ConfigurationException('Helpers path is not defined by key "paths: helpers"');

        self::$dataDir = $config['paths']['data'];
        self::$helpersDir = $config['paths']['helpers'];
        self::$testsDir = $config['paths']['tests'];

        self::autoloadHelpers();
        self::loadSuites();

        return $config;
    }

    protected static function loadConfigFile($file, $parentConfig)
    {
        $config = file_exists($file) ? Yaml::parse($file) : array();
        return self::mergeConfigs($parentConfig, $config);
    }

    protected static function autoloadHelpers()
    {
        spl_autoload_register(function ($class) {
            $matches = null;
            if (!preg_match('~\\\\?(\\w*?Helper)$~', $class, $matches)) return;
            $className = $matches[1];
            $helpers = Finder::create()->files()->name($className.'.php')->in(\Codeception\Configuration::helpersDir());
            foreach ($helpers as $helper) include_once($helper);
        });
    }

    protected static function loadSuites()
    {
        $suites = Finder::create()->files()->name('*.suite.yml')->in(self::$dir.DIRECTORY_SEPARATOR.self::$testsDir)->depth('< 1');
        self::$suites = array();
        foreach ($suites as $suite) {
            preg_match('~(.*?)(\.suite|\.suite\.dist)\.yml~', $suite->getFilename(), $matches);
            self::$suites[] = $matches[1];
        }
    }

    public static function suiteSettings($suite, $config)
    {
        if (!in_array($suite, self::$suites)) throw new \Exception("Suite $suite was not loaded");

        $globalConf = $config['settings'];
        foreach (array('modules','coverage', 'namespace') as $key) {
            if (isset($config[$key])) $globalConf[$key] = $config[$key];
        }

        $path = $config['paths']['tests'];

        $suiteConf = file_exists(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.yml") ? Yaml::parse(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.yml") : array();
        $suiteDistconf = file_exists(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.dist.yml") ? Yaml::parse(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.dist.yml") : array();

        $settings = self::mergeConfigs(self::$defaultSuiteSettings, $globalConf);
        $settings = self::mergeConfigs($settings, $suiteDistconf);
        $settings = self::mergeConfigs($settings, $suiteConf);

        $settings['path'] = self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $suite . DIRECTORY_SEPARATOR;

        return $settings;
    }

    public static function suites()
    {
        return self::$suites;
    }

    public static function modules($settings)
    {
        if (file_exists($guy = $settings['path'] . DIRECTORY_SEPARATOR . $settings['class_name'] . '.php')) require_once $guy;

        $modules = array();
        $namespace = isset($settings['namespace']) ? $settings['namespace'] : '';

        $moduleNames = $settings['modules']['enabled'];

        foreach ($moduleNames as $moduleName)
        {
            $classname = $namespace.'\\Codeception\\Module\\' . $moduleName;
            if (!class_exists($classname)) {
              $classname = '\\Codeception\\Module\\' . $moduleName;
              if (!class_exists($classname)) {
                  throw new ConfigurationException($moduleName.' could not be found and loaded');
              }
            }
            $moduleConfig = (isset($settings['modules']['config'][$moduleName])) ? $settings['modules']['config'][$moduleName] : array();
            $modules[$moduleName] = new $classname($moduleConfig);
        }

        return $modules;
    }

    public static function actions($modules)
    {
        $actions = array();

        foreach ($modules as $moduleName => $module) {
            $class   = new \ReflectionClass($module);
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                $inherit = $class->getStaticPropertyValue('includeInheritedActions');
                $only = $class->getStaticPropertyValue('onlyActions');
                $exclude = $class->getStaticPropertyValue('excludeActions');
                // NOT READY YET: $aliases = $class->getStaticPropertyValue('aliases');

                // exclude methods when they are listed as excluded
                if (in_array($method->name, $exclude)) continue;

                if (!empty($only)) {
                    // skip if method is not listed
                    if (!in_array($method->name, $only)) continue;
                } else {
                    // skip if method is inherited and inheritActions == false
                    if (!$inherit and $method->getDeclaringClass() != $class) continue;
                }

                // those with underscore at the beginning are considered as hidden
                if (strpos($method->name, '_') === 0) continue;

                $actions[$method->name] = $moduleName;
            }
        }

        return $actions;
    }

    public static function dataDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$dataDir . DIRECTORY_SEPARATOR;
    }

    public static function helpersDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$helpersDir . DIRECTORY_SEPARATOR;
    }

    public static function logDir()
    {
        if (!self::$logDir) throw new ConfigurationException("Path for logs not specified. Please, set log path in global config");
        $dir = realpath(self::$dir . DIRECTORY_SEPARATOR . self::$logDir) . DIRECTORY_SEPARATOR;
        if (!is_writable($dir)) {
            @mkdir($dir);
            @chmod($dir, 777);
        }
        if (!is_writable($dir)) {
            throw new ConfigurationException("Path for logs is not writable. Please, set appropriate access mode for log path.");
        }
        return $dir;
    }

    public static function projectDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR;
    }

    public static function isEmpty()
    {
        return !(bool)self::$testsDir;
    }

    protected static function mergeConfigs($a1, $a2)
    {
        if (!is_array($a1) || !is_array($a2))
            return $a2;
        $res = array();
        foreach ($a2 as $k2 => $v2) {
            if (!isset($a1[$k2])) { // if no such key
                $res[$k2] = $v2;
                unset($a1[$k2]);
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
