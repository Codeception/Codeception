<?php

namespace Codeception;

use Codeception\Util\Debug;
use Codeception\Util\Shared\Asserts;

abstract class Module
{
    use Asserts;

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

    protected $storage = array();

    protected $config = array();

    protected $backupConfig = array();

    protected $requiredFields = array();

    public function __construct($config = null)
    {
        $this->backupConfig = $this->config;
        if (is_array($config)) {
            $this->_setConfig($config);
        }
    }

    public function _setConfig($config)
    {
        $this->config = $this->backupConfig = array_merge($this->config, $config);
        $this->validateConfig();
    }

    public function _reconfigure($config)
    {
        $this->config =  array_merge($this->backupConfig, $config);
        $this->onReconfigure();
        $this->validateConfig();
    }

    protected function onReconfigure()
    {
        // update client on reconfigurations
    }

    public function _resetConfig()
    {
        $this->config = $this->backupConfig;
    }

    protected function validateConfig()
    {
        $fields = array_keys($this->config);
        if (array_intersect($this->requiredFields, $fields) != $this->requiredFields) {
            throw new Exception\ModuleConfig(
                get_class($this),
                "\nOptions: " . implode(', ', $this->requiredFields) . " are required\n
                Please, update the configuration and set all the required fields\n\n"
            );
        }
    }

    public function _getName()
    {
        $module = get_class($this);
         if (preg_match('@\\\\([\w]+)$@', $module, $matches)) {
             $module = $matches[1];
         }
         return $module;
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
        Debug::debug($message);
    }

    protected function debugSection($title, $message)
    {
        if (is_array($message) or is_object($message)) {
            $message = stripslashes(json_encode($message));
        }
        $this->debug("[$title] $message");
    }

    protected function hasModule($name)
    {
        return SuiteManager::hasModule($name);
    }

    protected function getModules()
    {
        return SuiteManager::$modules;
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
