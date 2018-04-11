<?php

namespace Codeception;

use Codeception\Exception\ConfigurationException;
use Codeception\Lib\ParamsLoader;
use Codeception\Util\Autoload;
use Codeception\Util\Template;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Exception\ParseException;
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
     * @var string Current project output directory.
     */
    protected static $outputDir = null;

    /**
     * @var string Current project data directory. This directory is used to hold
     * sql dumps and other things needed for current project tests.
     */
    protected static $dataDir = null;

    /**
     * @var string Directory with test support files like Actors, Helpers, PageObjects, etc
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
        'actor_suffix'=> 'Tester',
        'namespace'  => '',
        'include'    => [],
        'paths'      => [],
        'suites'     => [],
        'modules'    => [],
        'extensions' => [
            'enabled'  => [],
            'config'   => [],
            'commands' => [],
        ],
        'reporters'  => [
            'xml'    => 'Codeception\PHPUnit\Log\JUnit',
            'html'   => 'Codeception\PHPUnit\ResultPrinter\HTML',
            'report' => 'Codeception\PHPUnit\ResultPrinter\Report',
            'tap'    => 'PHPUnit\Util\Log\TAP',
            'json'   => 'PHPUnit\Util\Log\JSON',
        ],
        'groups'     => [],
        'settings'   => [
            'colors'                    => true,
            'bootstrap'                 => false,
            'strict_xml'                => false,
            'lint'                      => true,
            'backup_globals'            => true,
            'log_incomplete_skipped'    => false,
            'report_useless_tests'      => false,
            'disallow_test_output'      => false,
            'be_strict_about_changes_to_global_state' => false
        ],
        'coverage'   => [],
        'params'     => [],
        'gherkin'    => []
    ];

    public static $defaultSuiteSettings = [
        'actor'       => null,
        'class_name'  => null, // Codeception <2.3 compatibility
        'modules'     => [
            'enabled' => [],
            'config'  => [],
            'depends' => []
        ],
        'path'        => null,
        'namespace'   => null,
        'groups'      => [],
        'shuffle'     => false,
        'extensions'  => [ // suite extensions
            'enabled' => [],
            'config' => [],
        ],
        'error_level' => 'E_ALL & ~E_STRICT & ~E_DEPRECATED',
    ];

    protected static $params;

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
        self::$dir = $dir;

        $configDistFile = $dir . DIRECTORY_SEPARATOR . 'codeception.dist.yml';

        if (!(file_exists($configDistFile) || file_exists($configFile))) {
            throw new ConfigurationException("Configuration file could not be found.\nRun `bootstrap` to initialize Codeception.", 404);
        }

        // Preload config to retrieve params such that they are applied to codeception config file below
        $tempConfig = self::$defaultConfig;

        $distConfigContents = "";
        if (file_exists($configDistFile)) {
            $distConfigContents = file_get_contents($configDistFile);
            $tempConfig = self::mergeConfigs($tempConfig, self::getConfFromContents($distConfigContents, $configDistFile));
        }

        $configContents = "";
        if (file_exists($configFile)) {
            $configContents = file_get_contents($configFile);
            $tempConfig = self::mergeConfigs($tempConfig, self::getConfFromContents($configContents, $configFile));
        }
        self::prepareParams($tempConfig);

        // load config using params
        $config = self::mergeConfigs(self::$defaultConfig, self::getConfFromContents($distConfigContents, $configDistFile));
        $config = self::mergeConfigs($config, self::getConfFromContents($configContents, $configFile));

        if ($config == self::$defaultConfig) {
            throw new ConfigurationException("Configuration file is invalid");
        }

        self::$config = $config;

        // compatibility with suites created by Codeception < 2.3.0
        if (!isset($config['paths']['output']) and isset($config['paths']['log'])) {
            $config['paths']['output'] = $config['paths']['log'];
        }

        if (isset(self::$config['actor'])) {
            self::$config['actor_suffix'] = self::$config['actor']; // old compatibility
        }

        if (!isset($config['paths']['support']) and isset($config['paths']['helpers'])) {
            $config['paths']['support'] = $config['paths']['helpers'];
        }

        if (!isset($config['paths']['output'])) {
            throw new ConfigurationException('Output path is not defined by key "paths: output"');
        }

        self::$outputDir = $config['paths']['output'];

        // fill up includes with wildcard expansions
        $config['include'] = self::expandWildcardedIncludes($config['include']);

        // config without tests, for inclusion of other configs
        if (count($config['include'])) {
            self::$config = $config;
            if (!isset($config['paths']['tests'])) {
                 return $config;
            }
        }

        if (!isset($config['paths']['tests'])) {
            throw new ConfigurationException(
                'Tests directory is not defined in Codeception config by key "paths: tests:"'
            );
        }

        if (!isset($config['paths']['data'])) {
            throw new ConfigurationException('Data path is not defined Codeception config by key "paths: data"');
        }

        if (!isset($config['paths']['support'])) {
            throw new ConfigurationException('Helpers path is not defined by key "paths: support"');
        }

        self::$dataDir = $config['paths']['data'];
        self::$supportDir = $config['paths']['support'];
        self::$testsDir = $config['paths']['tests'];

        if (isset($config['paths']['envs'])) {
            self::$envsDir = $config['paths']['envs'];
        }

        Autoload::addNamespace(self::$config['namespace'], self::supportDir());
        self::loadBootstrap($config['settings']['bootstrap']);
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

    protected static function loadSuites()
    {
        $suites = Finder::create()
            ->files()
            ->name('*.{suite,suite.dist}.yml')
            ->in(self::$dir . DIRECTORY_SEPARATOR . self::$testsDir)
            ->depth('< 1')
            ->sortByName();

        self::$suites = [];

        foreach (array_keys(self::$config['suites']) as $suite) {
            self::$suites[$suite] = $suite;
        }

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
            throw new ConfigurationException("Suite $suite was not loaded");
        }

        // load global config
        $globalConf = $config['settings'];
        foreach (['modules', 'coverage', 'namespace', 'groups', 'env', 'gherkin', 'extensions'] as $key) {
            if (isset($config[$key])) {
                $globalConf[$key] = $config[$key];
            }
        }
        $settings = self::mergeConfigs(self::$defaultSuiteSettings, $globalConf);

        // load suite config
        $settings = self::loadSuiteConfig($suite, $config['paths']['tests'], $settings);
        // load from environment configs
        if (isset($config['paths']['envs'])) {
            $envConf = self::loadEnvConfigs(self::$dir . DIRECTORY_SEPARATOR . $config['paths']['envs']);
            $settings = self::mergeConfigs($settings, $envConf);
        }

        if (!$settings['actor']) {
            // Codeception 2.2 compatibility
            $settings['actor'] = $settings['class_name'];
        }

        if (!$settings['path']) {
            // take a suite path from its name
            $settings['path'] = $suite;
        }

        $config['paths']['tests'] = str_replace('/', DIRECTORY_SEPARATOR, $config['paths']['tests']);

        $settings['path'] = self::$dir . DIRECTORY_SEPARATOR . $config['paths']['tests']
            . DIRECTORY_SEPARATOR . $settings['path'] . DIRECTORY_SEPARATOR;



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
            return self::$envConfig[$path];
        }

        $envFiles = Finder::create()
            ->files()
            ->name('*.yml')
            ->in($path)
            ->depth('< 2');

        $envConfig = [];
        /** @var SplFileInfo $envFile */
        foreach ($envFiles as $envFile) {
            $env = str_replace(['.dist.yml', '.yml'], '', $envFile->getFilename());
            $envConfig[$env] = [];
            $envPath = $path;
            if ($envFile->getRelativePath()) {
                $envPath .= DIRECTORY_SEPARATOR . $envFile->getRelativePath();
            }
            foreach (['.dist.yml', '.yml'] as $suffix) {
                $envConf = self::getConfFromFile($envPath . DIRECTORY_SEPARATOR . $env . $suffix, null);
                if ($envConf === null) {
                    continue;
                }
                $envConfig[$env] = self::mergeConfigs($envConfig[$env], $envConf);
            }
        }

        self::$envConfig[$path] = ['env' => $envConfig];
        return self::$envConfig[$path];
    }

    /**
     * Loads configuration from Yaml data
     *
     * @param string $contents Yaml config file contents
     * @param string $filename which is supposed to be loaded
     * @return array
     * @throws ConfigurationException
     */
    protected static function getConfFromContents($contents, $filename = '(.yml)')
    {
        if (self::$params) {
            $template = new Template($contents, '%', '%');
            $template->setVars(self::$params);
            $contents = $template->produce();
        }

        try {
            return Yaml::parse($contents);
        } catch (ParseException $exception) {
            throw new ConfigurationException(
                sprintf(
                    "Error loading Yaml config from `%s`\n \n%s\nRead more about Yaml format https://goo.gl/9UPuEC",
                    $filename,
                    $exception->getMessage()
                )
            );
        }
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
            $yaml = file_get_contents($filename);
            return self::getConfFromContents($yaml, $filename);
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
     * Return list of enabled modules according suite config.
     *
     * @param array $settings suite settings
     * @return array
     */
    public static function modules($settings)
    {
        return array_filter(
            array_map(
                function ($m) {
                    return is_array($m) ? key($m) : $m;
                },
                $settings['modules']['enabled'],
                array_keys($settings['modules']['enabled'])
            ),
            function ($m) use ($settings) {
                if (!isset($settings['modules']['disabled'])) {
                    return true;
                }
                return !in_array($m, $settings['modules']['disabled']);
            }
        );
    }

    public static function isExtensionEnabled($extensionName)
    {
        return isset(self::$config['extensions'], self::$config['extensions']['enabled'])
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
        if (!self::$outputDir) {
            throw new ConfigurationException("Path for output not specified. Please, set output path in global config");
        }

        $dir = self::$outputDir . DIRECTORY_SEPARATOR;
        if (strcmp(self::$outputDir[0], "/") !== 0) {
            $dir = self::$dir . DIRECTORY_SEPARATOR . $dir;
        }

        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }

        if (!is_writable($dir)) {
            @chmod($dir, 0777);
        }

        if (!is_writable($dir)) {
            throw new ConfigurationException(
                "Path for output is not writable. Please, set appropriate access mode for output path."
            );
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
        if (!self::$envsDir) {
            return null;
        }
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
        self::$config = self::mergeConfigs(self::$config, $config);

        if (isset(self::$config['paths']['output'])) {
            self::$outputDir = self::$config['paths']['output'];
        }
        if (isset(self::$config['paths']['data'])) {
            self::$dataDir = self::$config['paths']['data'];
        }
        if (isset(self::$config['paths']['support'])) {
            self::$supportDir = self::$config['paths']['support'];
        }
        if (isset(self::$config['paths']['tests'])) {
            self::$testsDir = self::$config['paths']['tests'];
        }

        return self::$config;
    }

    public static function mergeConfigs($a1, $a2)
    {
        if (!is_array($a1)) {
            return $a2;
        }

        if (!is_array($a2)) {
            return $a1;
        }

        $res = [];

        // for sequential arrays
        if (isset($a1[0], $a2[0])) {
            return array_merge_recursive($a2, $a1);
        }

        // for associative arrays
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

    /**
     * Loads config from *.dist.suite.yml and *.suite.yml
     *
     * @param $suite
     * @param $path
     * @param $settings
     * @return array
     */
    protected static function loadSuiteConfig($suite, $path, $settings)
    {
        if (isset(self::$config['suites'][$suite])) {
            // bundled config
            return self::mergeConfigs($settings, self::$config['suites'][$suite]);
        }

        $suiteDistConf = self::getConfFromFile(
            self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.dist.yml"
        );
        $suiteConf = self::getConfFromFile(
            self::$dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$suite.suite.yml"
        );
        $settings = self::mergeConfigs($settings, $suiteDistConf);
        $settings = self::mergeConfigs($settings, $suiteConf);
        return $settings;
    }

    /**
     * Replaces wildcarded items in include array with real paths.
     *
     * @param $includes
     * @return array
     */
    protected static function expandWildcardedIncludes(array $includes)
    {
        if (empty($includes)) {
            return $includes;
        }
        $expandedIncludes = [];
        foreach ($includes as $include) {
            $expandedIncludes = array_merge($expandedIncludes, self::expandWildcardsFor($include));
        }
        return $expandedIncludes;
    }

    /**
     * Finds config files in given wildcarded include path.
     * Returns the expanded paths or the original if not a wildcard.
     *
     * @param $include
     * @return array
     * @throws ConfigurationException
     */
    protected static function expandWildcardsFor($include)
    {
        if (1 !== preg_match('/[\?\.\*]/', $include)) {
            return [$include,];
        }

        try {
            $configFiles = Finder::create()->files()
                ->name('/codeception(\.dist\.yml|\.yml)/')
                ->in(self::$dir . DIRECTORY_SEPARATOR . $include);
        } catch (\InvalidArgumentException $e) {
            throw new ConfigurationException(
                "Configuration file(s) could not be found in \"$include\"."
            );
        }

        $paths = [];
        foreach ($configFiles as $file) {
            $paths[] = codecept_relative_path($file->getPath());
        }

        return $paths;
    }

    private static function prepareParams($settings)
    {
        self::$params = [];
        $paramsLoader = new ParamsLoader();

        foreach ($settings['params'] as $paramStorage) {
            static::$params = array_merge(self::$params, $paramsLoader->load($paramStorage));
        }
    }
}
