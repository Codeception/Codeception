<?php

namespace Codeception;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Lib\ModuleContainer;
use Codeception\Util\Shared\Asserts;

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
     * @var ModuleContainer
     */
    protected $moduleContainer;

    /**
     * By setting it to false module wan't inherit methods of parent class.
     *
     * @var bool
     */
    public static $includeInheritedActions = true;

    /**
     * Allows to explicitly set what methods have this class.
     *
     * @var array
     */
    public static $onlyActions = [];

    /**
     * Allows to explicitly exclude actions from module.
     *
     * @var array
     */
    public static $excludeActions = [];

    /**
     * Allows to rename actions
     *
     * @var array
     */
    public static $aliases = [];

    protected $storage = [];

    protected $config = [];

    protected $backupConfig = [];

    protected $requiredFields = [];

    /**
     * Module constructor.
     *
     * Requires module container (to provide access between modules of suite) and config.
     *
     * @param ModuleContainer $moduleContainer
     * @param array|null $config
     */
    public function __construct(ModuleContainer $moduleContainer, $config = null)
    {
        $this->moduleContainer = $moduleContainer;

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
     * @param $config
     * @throws Exception\ModuleConfigException
     * @throws ModuleException
     */
    public function _setConfig($config)
    {
        $this->config = $this->backupConfig = array_merge($this->config, $config);
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
     * @param $config
     * @throws Exception\ModuleConfigException
     * @throws ModuleException
     */
    public function _reconfigure($config)
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
    public function _resetConfig()
    {
        $this->config = $this->backupConfig;
    }

    /**
     * Validates current config for required fields and required packages.
     *
     * @throws Exception\ModuleConfigException
     * @throws ModuleException
     */
    protected function validateConfig()
    {
        $fields = array_keys($this->config);
        if (array_intersect($this->requiredFields, $fields) != $this->requiredFields) {
            throw new Exception\ModuleConfigException(
                get_class($this),
                "\nOptions: " . implode(', ', $this->requiredFields) . " are required\n" .
                "Please, update the configuration and set all the required fields\n\n"
            );
        }
        if ($this instanceof RequiresPackage) {
            $errorMessage = '';
            foreach ($this->_requires() as $className => $package) {
                if (class_exists($className)) {
                    continue;
                }
                $errorMessage .= "Class $className can't be loaded, please add $package to composer.json\n";
            }
            if ($errorMessage) {
                throw new ModuleException($this, $errorMessage);
            }
        }
    }

    /**
     * Returns a module name for a Module, a class name for Helper
     *
     * @return string
     */
    public function _getName()
    {
        $moduleName = '\\' . get_class($this);

        if (strpos($moduleName, ModuleContainer::MODULE_NAMESPACE) === 0) {
            return substr($moduleName, strlen(ModuleContainer::MODULE_NAMESPACE));
        }

        return $moduleName;
    }

    /**
     * Checks if a module has required fields
     *
     * @return bool
     */
    public function _hasRequiredFields()
    {
        return !empty($this->requiredFields);
    }

    /**
     * **HOOK** triggered after module is created and configuration is loaded
     */
    public function _initialize()
    {
    }

    /**
     * **HOOK** executed before suite
     *
     * @param array $settings
     */
    public function _beforeSuite($settings = [])
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
     *
     * @param Step $step
     */
    public function _beforeStep(Step $step)
    {
    }

    /**
     * **HOOK** executed after step
     *
     * @param Step $step
     */
    public function _afterStep(Step $step)
    {
    }

    /**
     * **HOOK** executed before test
     *
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
    }

    /**
     * **HOOK** executed after test
     *
     * @param TestInterface $test
     */
    public function _after(TestInterface $test)
    {
    }

    /**
     * **HOOK** executed when test fails but before `_after`
     *
     * @param TestInterface $test
     * @param \Exception $fail
     */
    public function _failed(TestInterface $test, $fail)
    {
    }

    /**
     * Print debug message to the screen.
     *
     * @param $message
     */
    protected function debug($message)
    {
        codecept_debug($message);
    }

    /**
     * Print debug message with a title
     *
     * @param $title
     * @param $message
     */
    protected function debugSection($title, $message)
    {
        if (is_array($message) or is_object($message)) {
            $message = stripslashes(json_encode($message));
        }
        $this->debug("[$title] $message");
    }

    /**
     * Short text message to an amount of chars
     *
     * @param $message
     * @param $chars
     * @return string
     */
    protected function shortenMessage($message, $chars = 150)
    {
        return mb_substr($message, 0, $chars, 'utf-8');
    }

    /**
     * Checks that module is enabled.
     *
     * @param $name
     * @return bool
     */
    protected function hasModule($name)
    {
        return $this->moduleContainer->hasModule($name);
    }

    /**
     * Get all enabled modules
     *
     * @return array
     */
    protected function getModules()
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
     * @param $name
     * @return Module
     * @throws ModuleException
     */
    protected function getModule($name)
    {
        if (!$this->hasModule($name)) {
            throw new Exception\ModuleException(__CLASS__, "Module $name couldn't be connected");
        }
        return $this->moduleContainer->getModule($name);
    }

    /**
     * Get config values or specific config item.
     *
     * @param mixed $key
     * @return mixed the config item's value or null if it doesn't exist
     */
    public function _getConfig($key = null)
    {
        if (!$key) {
            return $this->config;
        }
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        return null;
    }

    protected function scalarizeArray($array)
    {
        foreach ($array as $k => $v) {
            if (!is_null($v) && !is_scalar($v)) {
                $array[$k] = (is_array($v) || $v instanceof \ArrayAccess)
                    ? $this->scalarizeArray($v)
                    : (string)$v;
            }
        }

        return $array;
    }
}
