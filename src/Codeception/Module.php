<?php
namespace Codeception;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Lib\ModuleContainer;
use Codeception\Util\Shared\Asserts;

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

    public function __construct(ModuleContainer $moduleContainer, $config = null)
    {
        $this->moduleContainer = $moduleContainer;

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
        $this->config = array_merge($this->backupConfig, $config);
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

    public function _getName()
    {
        $moduleName = '\\'.get_class($this);

        if (strpos($moduleName, ModuleContainer::MODULE_NAMESPACE) === 0) {
            return substr($moduleName, strlen(ModuleContainer::MODULE_NAMESPACE));
        }

        return $moduleName;
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
    public function _beforeSuite($settings = [])
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
    public function _before(TestInterface $test)
    {
    }

    // HOOK: after scenario
    public function _after(TestInterface $test)
    {
    }

    // HOOK: on fail
    public function _failed(TestInterface $test, $fail)
    {
    }

    protected function debug($message)
    {
        codecept_debug($message);
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
        return $this->moduleContainer->hasModule($name);
    }

    protected function getModules()
    {
        return $this->moduleContainer->all();
    }

    protected function getModule($name)
    {
        if (!$this->hasModule($name)) {
            throw new Exception\ModuleException(__CLASS__, "Module $name couldn't be connected");
        }
        return $this->moduleContainer->getModule($name);
    }

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
