<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Exception\ConfigurationException;
use Codeception\Lib\ParamsLoader;
use Codeception\Step\ConditionalAssertion;
use Codeception\Util\Autoload;
use Codeception\Util\PathResolver;
use Codeception\Util\Template;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

use function array_unique;

class Configuration
{
    /**
     * @var string[]
     */
    protected static array $suites = [];
    /**
     * @var array<string, mixed>|null Current configuration
     */
    protected static ?array $config = null;
    /**
     * @var array environmental files configuration cache
     */
    protected static array $envConfig = [];
    /**
     * @var string|null Directory containing main configuration file.
     * @see self::projectDir()
     */
    protected static ?string $dir = null;
    /**
     * @var string|null Directory of a base configuration file for the project with includes.
     * @see self::projectDir()
     */
    protected static ?string $baseDir = null;
    /**
     * @var string|null Current project output directory.
     */
    protected static ?string $outputDir = null;
    /**
     * @var string|null Current project data directory. This directory is used to hold
     * sql dumps and other things needed for current project tests.
     */
    protected static ?string $dataDir = null;
    /**
     * @var string|null Directory with test support files like Actors, Helpers, PageObjects, etc
     */
    protected static ?string $supportDir = null;
    /**
     * @var string|null Directory containing environment configuration files.
     */
    protected static ?string $envsDir = null;
    /**
     * @var string|null Directory containing tests and suites of the current project.
     */
    protected static ?string $testsDir = null;
    public static bool $lock = false;
    /**
     * @var array<string, mixed>|null
     */
    protected static ?array $params = null;

    /**
     * @var array<string, mixed>
     */
    public static array $defaultConfig = [
        'actor_suffix'      => 'Tester',
        'support_namespace' => null,
        'namespace'         => '',
        'include'           => [],
        'paths'             => [],
        'extends'           => null,
        'suites'            => [],
        'modules'           => [],
        'extensions'        => ['enabled' => [], 'config' => [], 'commands' => []],
        'groups'            => [],
        'bootstrap'         => false,
        'settings'          => [
            'colors'                                  => true,
            'bootstrap'                               => false,
            'strict_xml'                              => false,
            'lint'                                    => true,
            'backup_globals'                          => true,
            'report_useless_tests'                    => false,
            'be_strict_about_changes_to_global_state' => false,
            'shuffle'                                 => false,
        ],
        'coverage'          => [],
        'params'            => [],
        'gherkin'           => [],
    ];

    /**
     * @var array<string, mixed>
     */
    public static array $defaultSuiteSettings = [
        'actor'                              => null,
        'modules'                            => ['enabled' => [], 'config' => [], 'depends' => []],
        'step_decorators'                    => ConditionalAssertion::class,
        'path'                               => null,
        'extends'                            => null,
        'namespace'                          => null,
        'groups'                             => [],
        'formats'                            => [],
        'shuffle'                            => false,
        'extensions'                         => ['enabled' => [], 'config' => []],
        'error_level'                        => 'E_ALL & ~E_DEPRECATED',
        'convert_deprecations_to_exceptions' => false,
    ];

    /**
     * Loads global config file which is `codeception.yml` by default.
     * When config is already loaded - returns it.
     *
     * @return array<string, mixed>
     * @throws ConfigurationException
     */
    public static function config(?string $configFile = null): array
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
            $configFile = rtrim($configFile, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'codeception.yml';
        }
        $dir = realpath(dirname($configFile));
        if ($dir !== false) {
            self::$dir     = $dir;
            self::$baseDir ??= $dir;
        }

        $configDistFile = ($dir !== false ? $dir : dirname($configFile)) . DIRECTORY_SEPARATOR . 'codeception.dist.yml';
        if (!file_exists($configFile) && !file_exists($configDistFile)) {
            throw new ConfigurationException("Configuration file could not be found.\nRun bootstrap to initialize Codeception.", 404);
        }

        $tempConfig = self::$defaultConfig;
        $distConfigContents = '';
        if (file_exists($configDistFile)) {
            $distConfigContents = file_get_contents($configDistFile);
            if ($distConfigContents === false) {
                throw new ConfigurationException("Failed to read {$configDistFile}");
            }
            $tempConfig = self::mergeConfigs($tempConfig, self::getConfFromContents($distConfigContents, $configDistFile));
        }

        $configContents = '';
        if (file_exists($configFile)) {
            $configContents = file_get_contents($configFile);
            if ($configContents === false) {
                throw new ConfigurationException("Failed to read {$configFile}");
            }
            $tempConfig = self::mergeConfigs($tempConfig, self::getConfFromContents($configContents, $configFile));
        }

        self::prepareParams($tempConfig);

        $config = self::$defaultConfig;
        if ($distConfigContents !== '') {
            $config = self::mergeConfigs($config, self::getConfFromContents($distConfigContents, $configDistFile));
        }
        if ($configContents !== '') {
            $config = self::mergeConfigs($config, self::getConfFromContents($configContents, $configFile));
        }

        if ($config === self::$defaultConfig) {
            throw new ConfigurationException("Configuration file is invalid");
        }

        if (isset($config['extends'])) {
            $presetFilePath = codecept_absolute_path($config['extends']);
            if (file_exists($presetFilePath)) {
                $config = self::mergeConfigs(self::getConfFromFile($presetFilePath), $config);
            }
        }

        self::$config = $config;

        if (!isset(self::$config['paths']['support']) && isset(self::$config['paths']['helpers'])) {
            self::$config['paths']['support'] = self::$config['paths']['helpers'];
        }

        if (!isset(self::$config['paths']['output'])) {
            throw new ConfigurationException('Output path is not defined by key "paths: output"');
        }
        self::$outputDir = self::$config['paths']['output'];
        self::$config['include'] = self::expandWildcardedIncludes(self::$config['include']);

        if (!empty(self::$config['include']) && !isset(self::$config['paths']['tests'])) {
            return self::$config;
        }

        self::validatePaths();
        self::loadBootstrap(self::$config['bootstrap'], self::testsDir());
        self::loadSuites();

        return self::$config;
    }

    /**
     * @throws ConfigurationException
     */
    public static function loadBootstrap(string|false $bootstrap, string $path): void
    {
        if (!$bootstrap) {
            return;
        }
        $file = PathResolver::isPathAbsolute($bootstrap)
            ? $bootstrap
            : rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $bootstrap;
        if (!file_exists($file)) {
            throw new ConfigurationException("Bootstrap file {$file} can't be loaded");
        }
        require_once $file;
    }

    protected static function loadSuites(): void
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
        foreach ($suites as $suite) {
            preg_match('#(.*?)(\\.suite|\\.suite\\.dist)\\.yml#', $suite->getFilename(), $matches);
            self::$suites[$matches[1]] = $matches[1];
        }
    }

    private static function validatePaths(): void
    {
        if (empty(self::$config['paths']['tests'])) {
            throw new ConfigurationException('Tests directory is not defined in Codeception config by key "paths: tests"');
        }
        if (empty(self::$config['paths']['data'])) {
            throw new ConfigurationException('Data path is not defined in Codeception config by key "paths: data"');
        }
        if (empty(self::$config['paths']['support'])) {
            throw new ConfigurationException('Helpers path is not defined in Codeception config by key "paths: support"');
        }
        self::$dataDir    = self::$config['paths']['data'];
        self::$supportDir = self::$config['paths']['support'];
        self::$testsDir   = self::$config['paths']['tests'];
        self::$envsDir    = self::$config['paths']['envs'] ?? null;

        Autoload::addNamespace(
            self::$config['namespace'] . '\\' . self::$config['support_namespace'],
            self::supportDir()
        );
    }

    /**
     * Returns suite configuration. Requires suite name and global config used (Configuration::config)
     *
     * @return array<string, string>
     * @throws ConfigurationException
     */
    public static function suiteSettings(string $suite, array $config): array
    {
        if ($suite != $config['namespace'] && str_starts_with($suite, $config['namespace'])) {
            $suite = ltrim(substr($suite, strlen($config['namespace'])), '.');
        }
        if (!in_array($suite, self::$suites)) {
            throw new ConfigurationException("Suite {$suite} was not loaded");
        }

        $globalConf = $config['settings'];
        foreach (['modules', 'coverage', 'support_namespace', 'namespace', 'groups', 'env', 'gherkin', 'extensions'] as $key) {
            if (isset($config[$key])) {
                $globalConf[$key] = $config[$key];
            }
        }
        $settings = self::mergeConfigs(self::$defaultSuiteSettings, $globalConf);

        $settings = self::loadSuiteConfig($suite, $config['paths']['tests'], $settings);
        if (isset($config['paths']['envs'])) {
            $envConf = self::loadEnvConfigs(self::$dir . DIRECTORY_SEPARATOR . $config['paths']['envs']);
            $settings = self::mergeConfigs($settings, $envConf);
        }
        if (!$settings['path']) {
            $settings['path'] = $suite;
        }
        $config['paths']['tests'] = str_replace('/', DIRECTORY_SEPARATOR, (string) $config['paths']['tests']);
        $settings['path'] = self::$dir . DIRECTORY_SEPARATOR . $config['paths']['tests'] . DIRECTORY_SEPARATOR . $settings['path'] . DIRECTORY_SEPARATOR;
        $settings['suite'] = $suite;
        $settings['suite_namespace'] = $settings['namespace'] . '\\' . $suite;

        return $settings;
    }

    /**
     * Loads environments configuration from set directory
     *
     * @param string $path Path to the directory
     * @return array<string, mixed>
     */
    protected static function loadEnvConfigs(string $path): array
    {
        if (isset(self::$envConfig[$path])) {
            return self::$envConfig[$path];
        }
        if (!is_dir($path)) {
            self::$envConfig[$path] = [];
            return self::$envConfig[$path];
        }
        $envFiles = Finder::create()->files()->name('*.yml')->in($path)->depth('< 2');
        $envConfig = [];
        foreach ($envFiles as $envFile) {
            $env = str_replace(['.dist.yml', '.yml'], '', $envFile->getFilename());
            $envConfig[$env] = [];
            $envPath = $path . ($envFile->getRelativePath() !== '' ? DIRECTORY_SEPARATOR . $envFile->getRelativePath() : '');
            foreach (['.dist.yml', '.yml'] as $suffix) {
                $envConf = self::getConfFromFile($envPath . DIRECTORY_SEPARATOR . $env . $suffix);
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
     * @return array<string, mixed>
     * @throws ConfigurationException
     */
    protected static function getConfFromContents(string $contents, string $filename = '(.yml)'): array
    {
        if (self::$params) {
            $template = new Template($contents, "'%", "%'", 'json_encode');
            $template->setVars(self::$params);
            $contents = $template->produce();
            $template = new Template($contents, '"%', '%"', 'json_encode');
            $template->setVars(self::$params);
            $contents = $template->produce();
            $template = new Template($contents, '%', '%');
            $template->setVars(self::$params);
            $contents = $template->produce();
        }
        try {
            $conf = Yaml::parse($contents);
        } catch (ParseException $e) {
            throw new ConfigurationException(
                sprintf(
                    "Error loading Yaml config from %s\n\n%s\nRead more about Yaml format https://goo.gl/9UPuEC",
                    $filename,
                    $e->getMessage()
                )
            );
        }
        if (!is_array($conf)) {
            throw new ConfigurationException("Configuration file {$filename} is invalid or empty.");
        }
        return $conf;
    }

    /**
     * Loads configuration from Yaml file or returns given value if the file doesn't exist
     *
     * @param array<string, mixed> $nonExistentValue Value used if filename is not found
     * @return array<string, mixed>
     * @throws ConfigurationException
     */
    protected static function getConfFromFile(string $filename, array $nonExistentValue = []): array
    {
        if (!file_exists($filename)) {
            return $nonExistentValue;
        }
        $contents = file_get_contents($filename);
        if ($contents === false) {
            throw new ConfigurationException("Failed to read {$filename}");
        }
        return self::getConfFromContents($contents, $filename);
    }

    /**
     * @return string[]
     */
    public static function suites(): array
    {
        return self::$suites;
    }

    /**
     * Return list of enabled modules according suite config.
     *
     * @param array<string, mixed> $settings Suite settings
     * @return string[]
     */
    public static function modules(array $settings): array
    {
        return array_filter(
            array_map(
                fn($m): mixed => is_array($m) ? key($m) : $m,
                $settings['modules']['enabled'],
                array_keys($settings['modules']['enabled'])
            ),
            fn($m): bool => !isset($settings['modules']['disabled']) || !in_array($m, $settings['modules']['disabled'])
        );
    }

    public static function isExtensionEnabled(string $extensionName): bool
    {
        return isset(self::$config['extensions']['enabled']) &&
            in_array($extensionName, self::$config['extensions']['enabled']);
    }

    /**
     * Returns current path to `_data` dir.
     * Use it to store database fixtures, sql dumps, or other files required by your tests.
     */
    public static function dataDir(): string
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$dataDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Return current path to `_helpers` dir.
     * Helpers are custom modules.
     */
    public static function supportDir(): string
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$supportDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns actual path to current `_output` dir.
     * Use it in Helpers or Groups to save result or temporary files.
     *
     * @throws ConfigurationException
     */
    public static function outputDir(): string
    {
        if (self::$outputDir === '') {
            throw new ConfigurationException("Path for output not specified. Please, set output path in global config");
        }
        $dir = self::$outputDir . DIRECTORY_SEPARATOR;
        if (!codecept_is_path_absolute($dir)) {
            $dir = self::$dir . DIRECTORY_SEPARATOR . $dir;
        }
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        if (!is_writable($dir)) {
            @chmod($dir, 0777);
        }
        if (!is_writable($dir)) {
            throw new ConfigurationException("Path for output is not writable. Please, set appropriate access mode for output path: {$dir}");
        }
        return $dir;
    }

    /**
     * Returns path to the root of your project.
     * Basically returns path to current `codeception.yml` loaded.
     * Use this method instead of `__DIR__`, `getcwd()` or anything else.
     */
    public static function projectDir(): string
    {
        return self::$dir . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns path to the base dir for config which consists with included setup
     * Returns path to `codeception.yml` which was executed.
     * If config doesn't have "include" section the result is the same as `projectDir()`
     */
    public static function baseDir(): string
    {
        return self::$baseDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns path to tests directory
     */
    public static function testsDir(): string
    {
        return self::$dir . DIRECTORY_SEPARATOR . self::$testsDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Return current path to `_envs` dir.
     * Use it to store environment specific configuration.
     */
    public static function envsDir(): string
    {
        return self::$envsDir ? self::$dir . DIRECTORY_SEPARATOR . self::$envsDir . DIRECTORY_SEPARATOR : '';
    }

    /**
     * Is this a meta-configuration file that just points to other `codeception.yml`?
     * If so, it may have no tests by itself.
     */
    public static function isEmpty(): bool
    {
        return !self::$testsDir;
    }

    /**
     * Adds parameters to config
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public static function append(array $config = []): array
    {
        self::$config = self::mergeConfigs(self::$config ?? [], $config);
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

    public static function mergeConfigs(array $a1, array $a2): array
    {
        if (isset($a1[0], $a2[0])) {
            return array_values(array_unique(array_merge_recursive($a2, $a1), SORT_REGULAR));
        }
        $res = [];
        foreach ($a2 as $k2 => $v2) {
            if (!isset($a1[$k2]) || !is_array($a1[$k2])) {
                $res[$k2] = $v2;
                unset($a1[$k2]);
                continue;
            }
            if (is_array($v2)) {
                $res[$k2] = self::mergeConfigs($a1[$k2], $v2);
                unset($a1[$k2]);
            }
        }
        foreach ($a1 as $k1 => $v1) {
            $res[$k1] = $v1;
        }
        return $res;
    }

    /**
     * Loads config from *.dist.suite.yml and *.suite.yml
     *
     * @param array<string ,mixed> $settings
     * @return array<string ,mixed>
     * @throws ConfigurationException
     */
    protected static function loadSuiteConfig(string $suite, string $path, array $settings): array
    {
        if (isset(self::$config['suites'][$suite])) {
            return self::mergeConfigs($settings, self::$config['suites'][$suite]);
        }
        $suiteDir = self::$dir . DIRECTORY_SEPARATOR . $path;
        $suiteDist = self::getConfFromFile($suiteDir . DIRECTORY_SEPARATOR . "{$suite}.suite.dist.yml");
        $suiteConf = self::getConfFromFile($suiteDir . DIRECTORY_SEPARATOR . "{$suite}.suite.yml");
        if (isset($suiteConf['extends'])) {
            $preset = PathResolver::isPathAbsolute($suiteConf['extends'])
                ? $suiteConf['extends']
                : realpath($suiteDir . DIRECTORY_SEPARATOR . $suiteConf['extends']);
            if ($preset === false) {
                throw new ConfigurationException(sprintf("Configuration file %s does not exist", $suiteConf['extends']));
            }
            if (file_exists($preset)) {
                $settings = self::mergeConfigs(self::getConfFromFile($preset), $settings);
            }
        }
        $settings = self::mergeConfigs($settings, $suiteDist);
        return self::mergeConfigs($settings, $suiteConf);
    }

    /**
     * Replaces wildcarded items in include array with real paths.
     *
     * @param string[] $includes
     * @return string[]
     * @throws ConfigurationException
     */
    protected static function expandWildcardedIncludes(array $includes): array
    {
        if ($includes === []) {
            return $includes;
        }
        $expanded = [];
        foreach ($includes as $include) {
            $expanded = array_merge($expanded, self::expandWildcardsFor($include));
        }
        return $expanded;
    }

    /**
     * Finds config files in given wildcarded include path.
     * Returns the expanded paths or the original if not a wildcard.
     *
     * @return string[]
     * @throws ConfigurationException
     */
    protected static function expandWildcardsFor(string $include): array
    {
        if (!preg_match('#[?.*]#', $include)) {
            return [$include];
        }
        try {
            $finder = Finder::create()->files()
                ->name('/codeception(\.dist\.yml|\.yml)/')
                ->in(self::$dir . DIRECTORY_SEPARATOR . $include);
        } catch (InvalidArgumentException) {
            throw new ConfigurationException("Configuration file(s) could not be found in \"{$include}\".");
        }
        $paths = [];
        foreach ($finder as $file) {
            $paths[] = codecept_relative_path($file->getPath());
        }
        return array_unique($paths);
    }

    /**
     * @param array<string, mixed> $settings
     * @throws ConfigurationException
     */
    private static function prepareParams(array $settings): void
    {
        self::$params = [];
        foreach ($settings['params'] as $paramStorage) {
            self::$params = array_merge(self::$params, ParamsLoader::load($paramStorage));
        }
    }
}
