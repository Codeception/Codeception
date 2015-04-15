<?php

namespace Codeception;

use Codeception\Exception\ConfigurationException as ConfigurationException;
use Codeception\Util\Autoload;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class Configuration
{
    protected static $suites = [];

    /**
     * @var array Current configuration
     */
    protected static $config = null;

    /**
     * @var array environmental files configuration cache
     */
    protected static $envConfig = [];

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
    protected static $supportDir = null;

    /**
     * @var string Directory containing environment configuration files.
     */
    protected static $envsDir = null;

    /**
     * @var string Directory containing tests and suites of the current project.
     */
    protected static $testsDir = null;

    public static $lock = false;

    protected static $di;

    /**
     * @var array Default config
     */
    public static $defaultConfig = [
        'actor'      => 'Guy',
        'namespace'  => '',
        'include'    => [],
        'paths'      => [
            'envs' => 'tests/_envs',
        ],
        'modules'    => [],
        'extensions' => [
            'enabled' => [],
            'config'  => [],
        ],
        'reporters'  => [
            'xml'    => 'Codeception\PHPUnit\Log\JUnit',
            'html'   => 'Codeception\PHPUnit\ResultPrinter\HTML',
            'tap'    => 'PHPUnit_Util_Log_TAP',
            'json'   => 'PHPUnit_Util_Log_JSON',
            'report' => 'Codeception\PHPUnit\ResultPrinter\Report',
        ],
        'groups'     => [],
        'settings'   => [
            'colors'     => false,
            'log'        => false, // deprecated
            'bootstrap'  => '_bootstrap.php',
            'strict_xml' => false
        ],
        'coverage'   => []
    ];

    public static $defaultSuiteSettings = [
        'class_name'  => 'NoGuy',
        'modules'     => [
            'enabled' => [],
            'config'  => [],
            'depends' => []
        ],
        'namespace'   => null,
        'path'        => '',
        'groups'      => [],
        'shuffle'     => false,
        'suite_class' => '\PHPUnit_Framework_TestSuite',
        'error_level' => 'E_ALL & ~E_STRICT & ~E_DEPRECATED',
    ];

    /**
     * Loads global config file which is `codeception.yml` by default.
     * When config is already loaded - returns it.
     *
     * @param null $configFile
     * @return array
     * @throws Exception\ConfigurationException
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

        if (!(file_exists($configDistFile) || file_exists($configFile))) {
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

        // compatibility with 1.x, 2.0
        if (!isset($config['paths']['support']) and isset($config['paths']['helpers'])) {
            $config['paths']['support'] = $config['paths']['helpers'];
        }

        if (!isset($config['paths']['support'])) {
            throw new ConfigurationException('Helpers path is not defined by key "paths: support"');
        }

        self::$dataDir = $config['paths']['data'];
        self::$supportDir = $config['paths']['support'];
        self::$envsDir = $config['paths']['envs'];
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
        $bootstrap = self::$dir . DIRECTORY_SEPARATOR . self::$testsDir . DIRECTORY_SEPARATOR . $bootstrap;
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
    }

    protected static function loadConfigFile($file, $parentConfig)
    {
        $config = file_exists($file) ? Yaml::parse(file_get_contents($file)) : [];
        return self::mergeConfigs($parentConfig, $config);
    }

    protected static function autoloadHelpers()
    {
        $namespace = self::$config['namespace'];
        Autoload::addNamespace($namespace, self::supportDir());

        // deprecated
        Autoload::addNamespace($namespace . '\Codeception\Module', self::supportDir());
    }

    protected static function loadSuites()
    {
        $suites = Finder::create()->files()->name('*.{suite,suite.dist}.yml')->in(self::$dir . DIRECTORY_SEPARATOR . self::$testsDir)->depth('< 1');
        self::$suites = [];

        /** @var SplFileInfo $suite */
        foreach ($suites as $suite) {
            preg_match('~(.*?)(\.suite|\.suite\.dist)\.yml~', $suite->getFilename(), $matches);
            self::$suites[$matches[1]] = $matches[1];
        }
    }

    /**
     * Returns suite configuration. Requires suite name and global config used (Configuration::config)
     *
     * @param string $suite
     * @param array $config
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

        foreach (['modules', 'coverage', 'namespace', 'groups', 'env'] as $key) {
            if (isset($config[$key])) {
                $globalConf[$key] = $config[$key];
            }
        }

        $path = $config['paths']['tests'];

        $suiteConf = self::getConfFromFile(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.yml");
        $suiteDistconf = self::getConfFromFile(self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.dist.yml");
        $envConf = self::loadEnvConfigs(self::$dir . DIRECTORY_SEPARATOR . $config['paths']['envs']);

        $settings = self::mergeConfigs(self::$defaultSuiteSettings, $globalConf);
        $settings = self::mergeConfigs($settings, $envConf);
        $settings = self::mergeConfigs($settings, $suiteDistconf);
        $settings = self::mergeConfigs($settings, $suiteConf);

        $settings['path'] = self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $suite . DIRECTORY_SEPARATOR;

        return $settings;
    }

    /**
     * Loads environments configuration from set directory
     *
     * @param string $path path to the directory
     * @return array
     */
    protected static function loadEnvConfigs($path)
    {
        if (isset(self::$envConfig[$path])) {
            return self::$envConfig[$path];
        }
        if (!is_dir($path)) {
            self::$envConfig[$path] = [];
        } else {
            $envFiles = Finder::create()
                ->files()
                ->name('*{.dist}.yml')
                ->in($path)
                ->depth('< 1');

            $envs = [];
            /** @var SplFileInfo $envFile */
            foreach ($envFiles as $envFile) {
                preg_match('~^(.*?)(\.dist)?\.yml$~', $envFile->getFilename(), $matches);
                $envs[] = $matches[1];
            }

            $envConfig = [];
            foreach ($envs as $env) {
                $envConfig[$env] = [];
                $envConf = self::getConfFromFile($path . DIRECTORY_SEPARATOR . $env . '.dist.yml', null);
                if ($envConf !== null) {
                    $envConfig[$env] = self::mergeConfigs($envConfig[$env], $envConf);
                }
                $envConf = self::getConfFromFile($path . DIRECTORY_SEPARATOR . $env . '.yml', null);
                if ($envConf !== null) {
                    $envConfig[$env] = self::mergeConfigs($envConfig[$env], $envConf);
                }
            }

            self::$envConfig[$path] = ['env' => $envConfig];
        }
        return self::$envConfig[$path];
    }

    /**
     * Loads configuration from Yaml file or returns given value if the file doesn't exist
     *
     * @param string $filename filename
     * @param mixed $nonExistentValue value used if filename is not found
     * @return array
     */
    protected static function getConfFromFile($filename, $nonExistentValue = [])
    {
        if (file_exists($filename)) {
            return Yaml::parse(file_get_contents($filename));
        }
        return $nonExistentValue;
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
            return [];
        }

        $environments = [];

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
    public static function modules(&$settings)
    {
        return $settings['modules']['enabled'];
    }

    public static function isExtensionEnabled($extensionName)
    {
        return isset(self::$config['extensions'])
        && isset(self::$config['extensions']['enabled'])
        && in_array($extensionName, self::$config['extensions']['enabled']);
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
    public static function supportDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$supportDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns actual path to current `_output` dir.
     * Use it in Helpers or Groups to save result or temporary files.
     *
     * @return string
     * @throws Exception\ConfigurationException
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
     * Return current path to `_envs` dir.
     * Use it to store environment specific configuration.
     *
     * @return string
     */
    public static function envsDir()
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$envsDir . DIRECTORY_SEPARATOR;
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
    public static function append(array $config = [])
    {
        return self::$config = self::mergeConfigs(self::$config, $config);
    }

    public static function mergeConfigs($a1, $a2)
    {
        if (!is_array($a1) || !is_array($a2)) {
            return $a2;
        }

        $res = [];

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
