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
        'namespace' => null,
        'paths' => array(),
        'modules' => array(),
        'settings' => array(
            'colors' => false,
            'log' => false
        )
    );

    public static $defaultSuiteSettings = array(
        'class_name' => 'NoGuy',
        'modules' => array(),
        'bootstrap' => '_bootstrap.php',
        'suite_class' => '\PHPUnit_Framework_TestSuite',
        'error_level' => 'E_ALL & ~E_STRICT & ~E_DEPRECATED',
    );

    public static function config($config_file = null)
    {
        if (self::$config) return self::$config;

        if ($config_file === null) $config_file = getcwd() . DIRECTORY_SEPARATOR . 'codeception.yml';
        if (is_dir($config_file)) $config_file = $config_file . DIRECTORY_SEPARATOR . 'codeception.yml';
        $dir = dirname($config_file);
        $dist_file = $dir . DIRECTORY_SEPARATOR . 'codeception.yml';

        $distConfig = file_exists('codeception.dist.yml') ? Yaml::parse('codeception.dist.yml') : array();


        $config = file_exists($config_file) ? Yaml::parse($config_file) : array();
        $config = self::mergeConfigs(self::mergeConfigs(self::$defaultConfig, $distConfig), $config);


        if (empty($config)) throw new ConfigurationException("Configuration file is invalid");
        if (!isset($config['paths']['tests'])) throw new ConfigurationException('Tests directory is not defined in Codeception config by key "paths: tests:"');
        if (!isset($config['paths']['data'])) throw new ConfigurationException('Data path is not defined Codeception config by key "paths: data"');
        if (!isset($config['paths']['log'])) throw new ConfigurationException('Log path is not defined by key "paths: log"');

        self::$config = $config;
        self::$dataDir = $config['paths']['data'];
        self::$logDir = $config['paths']['log'];
        self::$helpersDir = $config['paths']['helpers'];
        self::$testsDir = $config['paths']['tests'];
        self::$dir = $dir;

        self::autoloadHelpers();
        self::loadSuites();

        return $config;
    }

    protected static function autoloadHelpers()
    {
        $helpers = Finder::create()->files()->name('*Helper.php')->in(self::helpersDir());
        spl_autoload_register(function ($class) {
            if (!preg_match('~Helper$~', $class)) return;
            $helpers = Finder::create()->files()->name($class.'.php')->in(self::$helpersDir);
        });
        // foreach ($helpers as $helper) include_once($helper);
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

    public static function suiteSettings($suite, $config)
    {
        if (!in_array($suite, self::$suites)) throw new \Exception("Suite $suite was not loaded");

        $defaults = self::defaultSuiteSettings();

        $globalConf = $config['settings'];
        foreach (array('modules','coverage', 'namespace') as $key) {
            if (isset($config[$key])) $globalConf[$key] = $config[$key];
        }

        $path = $config['paths']['tests'];

        $suiteConf = file_exists(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.yml") ? Yaml::parse(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.yml") : array();
        $suiteDistconf = file_exists(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.dist.yml") ? Yaml::parse(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.dist.yml") : array();

        $settings = self::mergeConfigs($defaults, $globalConf);
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
        $defaults = array('modules' => array('enabled' => array(), 'config' => array()));
        if (!isset($settings['modules'])) throw new ConfigurationException('No modules configured!');

        if (file_exists($guy = $settings['path'] . DIRECTORY_SEPARATOR . $settings['class_name'] . '.php')) require_once $guy;
        // if (!class_exists($settings['class_name'])) throw new \Codeception\Exception\Configuration("No guys were found. Tried to find {$settings['class_name']} but he was not there.");

        $modules = array();

        $settings = self::mergeConfigs($defaults, $settings);

        $moduleNames = $settings['modules']['enabled'];

        foreach ($moduleNames as $moduleName)
        {
            $classname = '\Codeception\Module\\' . $moduleName;
            $moduleConfig = (isset($settings['modules']['config'][$moduleName])) ? $settings['modules']['config'][$moduleName] : array();

            $modules[$moduleName] = static::createModule($classname, $moduleConfig);
        }

        return $modules;
    }

    /**
     * Creates new module instance on given parameters. Also ensure that all module required
     * fields are set, if not throws exception.
     * @param string $class module class
     * @param array $config config array. Defaults to empty array.
     * @return \Codeception\Module created module
     * @throws \Codeception\Exception\ModuleConfig if module required fields were not set.
     */
    public static function createModule($class,$config=array())
    {
        $moduleName  = explode('\\', $class);
        $moduleName = end($moduleName);
        $module = new $class;

        if ($config !== array())
            $module->_setConfig($config);
        else if ($module->_hasRequiredFields())
            throw new \Codeception\Exception\ModuleConfig($moduleName, "Module $moduleName is not configured. Please check out it's required fields");

       return $module;
    }

    public static function actions($modules)
    {
        $actions = array();

        foreach ($modules as $moduleName => $module) {
            $class   = new \ReflectionClass('\Codeception\Module\\' . $moduleName);
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                // those with underscore at the beginning are considered as hidden
                if (strpos($method->name, '_') === 0) {
                    continue;
                }

                $actions[$method->name] = $moduleName;
            }
        }

        return $actions;
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
