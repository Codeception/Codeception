<?php

namespace Codeception;

use Codeception\Exception\Configuration as ConfigurationException;
use Codeception\Util\Autoload;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;

class Configuration
{
    protected static $suites = array();

    /**
     * @var array Current configuration
     */
    protected static $config = null;

    /**
     * @var string Directory containing main configuration file.
     * @see self::projectDir() 
     */
    protected static $dir = null;

    /**
     * @var string Current project logs directory.
     */
    protected static $logDir = null;

    /**
     * @var string Current project data directory. This directory is used to hold
     * sql dumps and other things needed for current project tests.
     */
    protected static $dataDir = null;

    /**
     * @var string Directory containing helpers. Helpers will be autoloaded if they have suffix "Helper".
     */
    protected static $helpersDir = null;

    /**
     * @var string Directory containing tests and suites of the current project.
     */
    protected static $testsDir = null;

    public static $lock = false;

    /**
     * @var array Default config
     */
    public static $defaultConfig = array(
        'actor' => 'Guy',
        'namespace' => '',
        'include' => array(),
        'paths' => array(),
        'modules' => array(),
        'extensions' => array(
            'enabled' => array(),
            'config' => array(),
        ),
        'groups' => [],
        'settings' => array(
            'colors' => false,
            'log' => false, // deprecated
            'strict_xml' => false,
            'bootstrap' => false
        ),
        'coverage' => []
    );

    public static $defaultSuiteSettings = array(
        'class_name' => 'NoGuy',
        'modules' => array(
            'enabled' => array(),
            'config' => array(),
        ),
        'namespace' => null,
        'path' => '',
        'groups' => [],
        'suite_class' => '\PHPUnit_Framework_TestSuite',
        'error_level' => 'E_ALL & ~E_STRICT & ~E_DEPRECATED',
    );

    /**
     * Loads global config file which is `codeception.yml` by default.
     * When config is already loaded - returns it.
     *
     * @param null $configFile
     * @return array
     * @throws Exception\Configuration
     */
    public static function config($configFile = null)
    {
        if (!$configFile && self::$config) {
            return self::$config;
        }

        if (self::$config && self::$lock) {
            return self::$config;
        }

        if ($configFile === null) {
            $configFile = getcwd() . DIRECTORY_SEPARATOR . 'codeception.yml';
        }

        if (is_dir($configFile)) {
            $configFile = $configFile . DIRECTORY_SEPARATOR . 'codeception.yml';
        }

        $dir = realpath(dirname($configFile));

        $configDistFile = $dir . DIRECTORY_SEPARATOR . 'codeception.dist.yml';

        if (! (file_exists($configDistFile) || file_exists($configFile))) {
            throw new ConfigurationException("Configuration file could not be found.\nRun `bootstrap` to initialize Codeception.");
        }

        $config = self::loadConfigFile($configDistFile, self::$defaultConfig);
        $config = self::loadConfigFile($configFile, $config);

        if ($config == self::$defaultConfig) {
            throw new ConfigurationException("Configuration file is invalid");
        }

        self::$dir = $dir;
        self::$config = $config;

        if (!isset($config['paths']['log'])) {
            throw new ConfigurationException('Log path is not defined by key "paths: log"');
        }

        self::$logDir = $config['paths']['log'];

        // config without tests, for inclusion of other configs
        if (count($config['include']) and !isset($config['paths']['tests'])) {
            return $config;
        }

        if (!isset($config['paths']['tests'])) {
            throw new ConfigurationException('Tests directory is not defined in Codeception config by key "paths: tests:"');
        }

        if (!isset($config['paths']['data'])) {
            throw new ConfigurationException('Data path is not defined Codeception config by key "paths: data"');
        }

        if (!isset($config['paths']['helpers'])) {
           throw new ConfigurationException('Helpers path is not defined by key "paths: helpers"');
        }

        self::$dataDir = $config['paths']['data'];
        self::$helpersDir = $config['paths']['helpers'];
        self::$testsDir = $config['paths']['tests'];

        self::loadBootstrap($config['settings']['bootstrap']);
        self::autoloadHelpers();
        self::loadSuites();

        return $config;
    }

    protected static function loadBootstrap($bootstrap)
    {
        if (!$bootstrap) {
            return;
        }
        $bootstrap = self::$dir . DIRECTORY_SEPARATOR . self::$testsDir.DIRECTORY_SEPARATOR.$bootstrap;
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
    }

    protected static function loadConfigFile($file, $parentConfig)
    {
        $config = file_exists($file) ? Yaml::parse(file_get_contents($file)) : array();
        return self::mergeConfigs($parentConfig, $config);
    }

    protected static function autoloadHelpers()
    {
        Autoload::registerSuffix('Helper', self::helpersDir());
    }

    protected static function loadSuites()
    {
        $suites = Finder::create()->files()->name('*.{suite,suite.dist}.yml')->in(self::$dir.DIRECTORY_SEPARATOR.self::$testsDir)->depth('< 1');
        self::$suites = array();

        foreach ($suites as $suite) {
            preg_match('~(.*?)(\.suite|\.suite\.dist)\.yml~', $suite->getFilename(), $matches);
            self::$suites[$matches[1]] = $matches[1];
        }
    }

    /**
     * Returns suite configuration. Requires suite name and global config used (Configuration::config)
     *
     * @param $suite
     * @param $config
     * @return array
     * @throws \Exception
     */
    public static function suiteSettings($suite, $config)
    {
        // cut namespace name from suite name
        if ($suite != $config['namespace'] && substr($suite, 0, strlen($config['namespace'])) == $config['namespace']) {
            $suite = substr($suite, strlen($config['namespace']));
        }

        if (!in_array($suite, self::$suites)) {
            throw new \Exception("Suite $suite was not loaded");
        }

        $globalConf = $config['settings'];

        foreach (array('modules','coverage', 'namespace', 'groups', 'env') as $key) {
            if (isset($config[$key])) {
                $globalConf[$key] = $config[$key];
            }
        }

        $path = $config['paths']['tests'];

        $suiteConf = file_exists(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.yml")
            ? Yaml::parse(file_get_contents(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.yml"))
            : array();

        $suiteDistconf = file_exists(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.dist.yml")
            ? Yaml::parse(file_get_contents(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.dist.yml"))
            : array();

        $settings = self::mergeConfigs(self::$defaultSuiteSettings, $globalConf);
        $settings = self::mergeConfigs($settings, $suiteDistconf);
        $settings = self::mergeConfigs($settings, $suiteConf);

        $settings['path'] = self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $suite . DIRECTORY_SEPARATOR;

        return $settings;
    }

    /**
     * Returns all possible suite configurations according environment rules.
     * Suite configurations will contain `current_environment` key which specifies what environment used.
     *
     * @param $suite
     * @return array
     */
    public static function suiteEnvironments($suite)
    {
        $settings = self::suiteSettings($suite, self::config());

        if (!isset($settings['env']) || !is_array($settings['env'])) {
            return array();
        }

        $environments = array();

        foreach ($settings['env'] as $env => $envConfig) {
            $environments[$env] = $envConfig ? self::mergeConfigs($settings, $envConfig) : $settings;
            $environments[$env]['current_environment'] = $env;
        }

        return $environments;
    }

    public static function suites()
    {
        return self::$suites;
    }

    /**
     * Return instances of enabled modules according suite config.
     * Requires Guy class if it exists.
     * 
     * @param array $settings suite settings
     * @return array|\Codeception\Module[]
     */
    public static function modules($settings)
    {
        $modules = array();
        $namespace = isset($settings['namespace']) ? $settings['namespace'] : '';

        $moduleNames = $settings['modules']['enabled'];

        foreach ($moduleNames as $moduleName) {
            $moduleConfig = (isset($settings['modules']['config'][$moduleName])) ? $settings['modules']['config'][$moduleName] : array();
            $modules[$moduleName] = static::createModule($moduleName, $moduleConfig, $namespace);
        }

        return $modules;
    }

    /**
     * Creates new module and configures it.
     * Module class is searched and resolves according following rules:
     *
     * 1. if "class" element is fully qualified class name, it will be taken to create module;
     * 2. module class will be searched under default namespace, according $namespace parameter:
     * $namespace.'\Codeception\Module\' . $class;
     * 3. module class will be searched under Codeception module namespace, that is "\Codeception\Module".
     *
     * @param $class
     * @param array $config module configuration
     * @param string $namespace default namespace for module.
     * @throws Exception\Configuration
     * @return \Codeception\Module
     */
    public static function createModule($class, $config, $namespace = '')
    {
        $hasNamespace = (mb_strpos($class, '\\') !== false);

        if ($hasNamespace) {
            return new $class($config);
        }

        // try find module under users suite namespace setting
        $className = $namespace.'\\Codeception\\Module\\' . $class;

        if (!@class_exists($className)) {
            // fallback to default namespace
            $className = '\\Codeception\\Module\\' . $class;
            if (!@class_exists($className)) {
                throw new ConfigurationException($class.' could not be found and loaded');
            }
        }

        return new $className($config);
    }

    public static function isExtensionEnabled($extensionName)
    {
        return isset(self::$config['extensions'])
            && isset(self::$config['extensions']['enabled'])
            && in_array($extensionName, self::$config['extensions']['enabled']);
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

    /**
     * Returns current path to `_data` dir.
     * Use it to store database fixtures, sql dumps, or other files required by your tests.
     *
     * @return string
     */
    public static function dataDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$dataDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Return current path to `_helpers` dir.
     * Helpers are custom modules.
     *
     * @return string
     */
    public static function helpersDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$helpersDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns actual path to current `_output` dir.
     * Use it in Helpers or Groups to save result or temporary files.
     *
     * @return string
     * @throws Exception\Configuration
     */
    public static function outputDir()
    {
        if (!self::$logDir) {
            throw new ConfigurationException("Path for logs not specified. Please, set log path in global config");
        }
        $dir = self::$dir . DIRECTORY_SEPARATOR . self::$logDir . DIRECTORY_SEPARATOR;

        if (!is_writable($dir)) {
            @mkdir($dir);
            @chmod($dir, 0777);
        }

        if (!is_writable($dir)) {
            throw new ConfigurationException("Path for logs is not writable. Please, set appropriate access mode for log path.");
        }

        return $dir;
    }

    /**
     * Compatibility alias to `Configuration::logDir()`
     * @return string
     */
    public static function logDir()
    {
        return self::outputDir();
    }

    /**
     * Returns path to the root of your project.
     * Basically returns path to current `codeception.yml` loaded.
     * Use this method instead of `__DIR__`, `getcwd()` or anything else.
     * @return string
     */
    public static function projectDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns path to tests directory
     *
     * @return string
     */
    public static function testsDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$testsDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Is this a meta-configuration file that just points to other `codeception.yml`?
     * If so, it may have no tests by itself.
     *
     * @return bool
     */
    public static function isEmpty()
    {
        return !(bool)self::$testsDir;
    }

    /**
     * Adds parameters to config
     *
     * @param array $config
     * @return array
     */
    public static function append(array $config = array())
    {
        return self::$config = self::mergeConfigs(self::$config, $config);
    }

    public static function mergeConfigs($a1, $a2)
    {
        if (!is_array($a1) || !is_array($a2)) {
            return $a2;
        }

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

        foreach ($a1 as $k1 => $v1) { // only single elements here left
            $res[$k1] = $v1;
        }

        return $res;
    }

}
