<?php

namespace Codeception;

use Codeception\PHPUnit\AssertWrapper;

abstract class Module extends AssertWrapper
{
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
    public static $onlyActions = array();

    /**
     * Allows to explicitly exclude actions from module.
     *
     * @var array
     */
    public static $excludeActions = array();

    /**
     * Allows to rename actions
     *
     * @var array
     */
    public static $aliases = array();

    protected $debugStack = array();

    protected $storage = array();

    protected $config = array();

    protected $defaultConfig = array();

    protected $requiredFields = array();

    public function __construct($config = null)
    {
        if (is_array($config)) {
            $this->_setConfig($config);
        }
    }

    public function _setConfig($config)
    {
        $this->config = $this->defaultConfig = array_merge($this->config, $config);
        $this->validateConfig();
    }

    public function _reconfigure($config)
    {
        $this->config = array_merge($this->defaultConfig, $config);
        $this->validateConfig();
    }

    public function _resetConfig()
    {
        $this->config = $this->defaultConfig;
    }

    public function validateConfig()
    {
        $fields = array_keys($this->config);
        if (array_intersect($this->requiredFields, $fields) != $this->requiredFields) {
            throw new Exception\ModuleConfig(
                get_class($this),
                "\nOptions: " . implode(', ', $this->requiredFields) . " are required.\nPlease, update the configuration and set all the required fields"
            );
        }
    }

    public function _hasRequiredFields()
    {
        return !empty($this->requiredFields);
    }

    // HOOK: used after configuration is loaded
    public function _initialize()
    {
    }

    // HOOK: on every Guy class initialization
    public function _cleanup()
    {
    }

    // HOOK: before each suite
    public function _beforeSuite($settings = array())
    {
    }

    // HOOK: after suite
    public function _afterSuite()
    {
    }

    // HOOK: before every step
    public function _beforeStep(Step $step)
    {
    }

    // HOOK: after every  step
    public function _afterStep(Step $step)
    {
    }

    // HOOK: before scenario
    public function _before(TestCase $test)
    {
    }

    // HOOK: after scenario
    public function _after(TestCase $test)
    {
    }

    // HOOK: on fail
    public function _failed(TestCase $test, $fail)
    {
    }

    protected function debug($message)
    {
       
        $this->debugStack[] = $message;
    }

    protected function debugSection($title, $message)
    {
        $this->debug("[$title] $message");
    }

    public function _clearDebugOutput()
    {
        $this->debugStack = array();
    }

    public function _getDebugOutput()
    {
        $debugStack = $this->debugStack;
        $this->_clearDebugOutput();
        return $debugStack;
    }

    protected function getModules()
    {
        return SuiteManager::$modules;
    }

    protected function hasModule($name)
    {
        return SuiteManager::hasModule($name);
    }

    protected function getModule($name)
    {
        if (!$this->hasModule($name)) {
            throw new Exception\Module(__CLASS__, "Module $name couldn't be connected");
        }

        return SuiteManager::$modules[$name];
    }

    protected function scalarizeArray($array)
    {
        foreach ($array as $k => $v) {
            if (!is_scalar($v)) {
                $array[$k] = (is_array($v) || $v instanceof \ArrayAccess)
                    ? $this->scalarizeArray($v)
                    : (string)$v;
            }
        }

        return $array;
    }
}
