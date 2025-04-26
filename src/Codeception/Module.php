<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Lib\ModuleContainer;
use Codeception\Util\Shared\Asserts;
use Exception;

/**
 * Basic class for Modules and Helpers.
 * You must extend from it while implementing own helpers.
 *
 * Public methods of this class start with `_` prefix in order to ignore them in actor classes.
 * Module contains **HOOKS** which allow to handle test execution routine.
 *
 */
abstract class Module
{
    use Asserts;

    /**
     * By setting it to false module wan't inherit methods of parent class.
     */
    public static bool $includeInheritedActions = true;

    /**
     * Allows to explicitly set what methods have this class.
     */
    public static array $onlyActions = [];

    /**
     * Allows to explicitly exclude actions from module.
     */
    public static array $excludeActions = [];

    /**
     * Allows to rename actions
     */
    public static array $aliases = [];

    protected array $storage = [];

    protected array $config = [];

    protected array $backupConfig = [];

    protected array $requiredFields = [];

    /**
     * Module constructor.
     *
     * Requires module container (to provide access between modules of suite) and config.
     */
    public function __construct(protected ModuleContainer $moduleContainer, ?array $config = null)
    {
        $this->backupConfig = $this->config;
        if (is_array($config)) {
            $this->_setConfig($config);
        }
    }

    /**
     * Allows to define initial module config.
     * Can be used in `_beforeSuite` hook of Helpers or Extensions
     *
     * ```php
     * <?php
     * public function _beforeSuite($settings = []) {
     *     $this->getModule('otherModule')->_setConfig($this->myOtherConfig);
     * }
     * ```
     *
     * @throws ModuleConfigException|ModuleException
     */
    public function _setConfig(array $config): void
    {
        $this->config       = array_merge($this->config, $config);
        $this->backupConfig = $this->config;
        $this->validateConfig();
    }

    /**
     * Allows to redefine config for a specific test.
     * Config is restored at the end of a test.
     *
     * ```php
     * <?php
     * // cleanup DB only for specific group of tests
     * public function _before(Test $test) {
     *     if (in_array('cleanup', $test->getMetadata()->getGroups()) {
     *         $this->getModule('Db')->_reconfigure(['cleanup' => true]);
     *     }
     * }
     * ```
     *
     * @throws ModuleConfigException|ModuleException
     */
    public function _reconfigure(array $config): void
    {
        $this->config = array_merge($this->backupConfig, $config);
        $this->onReconfigure();
        $this->validateConfig();
    }

    /**
     * HOOK to be executed when config changes with `_reconfigure`.
     */
    protected function onReconfigure()
    {
        // update client on reconfigurations
    }

    /**
     * Reverts config changed by `_reconfigure`
     */
    public function _resetConfig(): void
    {
        $this->config = $this->backupConfig;
    }

    /**
     * Validates current config for required fields and required packages.
     *
     * @throws ModuleConfigException|ModuleException
     */
    protected function validateConfig(): void
    {
        if (($missing = array_diff($this->requiredFields, array_keys($this->config))) !== []) {
            throw new ModuleConfigException(
                static::class,
                sprintf(
                    "\nOptions: %s are required\nPlease, update the configuration and set all the required fields\n\n",
                    implode(', ', $missing)
                )
            );
        }

        if ($this instanceof RequiresPackage) {
            $errors = '';
            foreach ($this->_requires() as $className => $package) {
                if (!class_exists($className)) {
                    $errors .= "Class {$className} can't be loaded, please add {$package} to composer.json\n";
                }
            }
            if ($errors !== '') {
                throw new ModuleException($this, $errors);
            }
        }
    }

    /**
     * Returns a module name for a Module, a class name for Helper
     */
    public function _getName(): string
    {
        $moduleName = '\\' . static::class;

        return str_starts_with($moduleName, ModuleContainer::MODULE_NAMESPACE)
            ? substr($moduleName, strlen(ModuleContainer::MODULE_NAMESPACE))
            : $moduleName;
    }

    /**
     * Checks if a module has required fields
     */
    public function _hasRequiredFields(): bool
    {
        return $this->requiredFields !== [];
    }

    /**
     * **HOOK** triggered after module is created and configuration is loaded
     */
    public function _initialize()
    {
    }

    /**
     * **HOOK** executed before suite
     */
    public function _beforeSuite(array $settings = [])
    {
    }

    /**
     * **HOOK** executed after suite
     */
    public function _afterSuite()
    {
    }

    /**
     * **HOOK** executed before step
     */
    public function _beforeStep(Step $step)
    {
    }

    /**
     * **HOOK** executed after step
     */
    public function _afterStep(Step $step)
    {
    }

    /**
     * **HOOK** executed before test
     */
    public function _before(TestInterface $test)
    {
    }

    /**
     * **HOOK** executed after test
     */
    public function _after(TestInterface $test)
    {
    }

    /**
     * **HOOK** executed when test fails but before `_after`
     */
    public function _failed(TestInterface $test, Exception $fail)
    {
    }

    /**
     * Print debug message to the screen.
     */
    protected function debug(mixed $message): void
    {
        codecept_debug($message);
    }

    /**
     * Print debug message with a title
     */
    protected function debugSection(string $title, mixed $msg): void
    {
        if (is_array($msg) || is_object($msg)) {
            $msg = json_encode($msg, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_SLASHES);
        }
        $this->debug("[{$title}] {$msg}");
    }

    /**
     * Short text message to an amount of chars
     */
    protected function shortenMessage(string $message, int $chars = 150): string
    {
        return mb_substr($message, 0, $chars, 'utf-8');
    }

    /**
     * Checks that module is enabled.
     */
    protected function hasModule(string $name): bool
    {
        return $this->moduleContainer->hasModule($name);
    }

    /**
     * Get all enabled modules
     */
    protected function getModules(): array
    {
        return $this->moduleContainer->all();
    }

    /**
     * Get another module by its name:
     *
     * ```php
     * <?php
     * $this->getModule('WebDriver')->_findElements('.items');
     * ```
     *
     * @throws ModuleException
     */
    protected function getModule(string $name): Module
    {
        if (!$this->hasModule($name)) {
            $this->moduleContainer->throwMissingModuleExceptionWithSuggestion(self::class, $name);
        }
        return $this->moduleContainer->getModule($name);
    }

    /**
     * Get config values or specific config item.
     *
     * @param string|null $key
     * @return mixed the config item's value or null if it doesn't exist
     */
    public function _getConfig(?string $key = null): mixed
    {
        return $key === null ? $this->config : ($this->config[$key] ?? null);
    }

    protected function scalarizeArray(array $array): array
    {
        array_walk_recursive(
            $array,
            static function (&$value): void {
                if (!is_null($value) && !is_scalar($value)) {
                    $value = (string)$value;
                }
            }
        );

        return $array;
    }
}
