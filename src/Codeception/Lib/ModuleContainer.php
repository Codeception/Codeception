<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ModuleConflictException;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Util\Annotation;

/**
 * Class ModuleContainer
 * @package Codeception\Lib
 */
class ModuleContainer
{
    /**
     * @var string
     */
    const MODULE_NAMESPACE = '\\Codeception\\Module\\';

    /**
     * @var array
     */
    private $config;

    /**
     * @var Di
     */
    private $di;

    /**
     * @var array
     */
    private $modules = [];

    /**
     * @var array
     */
    private $active = [];

    /**
     * @var array
     */
    private $actions = [];

    /**
     * Constructor.
     *
     * @param Di $di
     * @param array $config
     */
    public function __construct(Di $di, $config)
    {
        $this->di = $di;
        $this->di->set($this);
        $this->config = $config;
    }

    /**
     * Create a module.
     *
     * @param string $moduleName
     * @param bool $active
     * @return \Codeception\Module
     * @throws \Codeception\Exception\ConfigurationException
     * @throws \Codeception\Exception\ModuleException
     * @throws \Codeception\Exception\ModuleRequireException
     * @throws \Codeception\Exception\InjectionException
     */
    public function create($moduleName, $active = true)
    {
        $this->active[$moduleName] = $active;

        $moduleClass = $this->getModuleClass($moduleName);
        if (!class_exists($moduleClass)) {
            throw new ConfigurationException("Module $moduleName could not be found and loaded");
        }

        $config = $this->getModuleConfig($moduleName);

        if (empty($config) && !$active) {
            // For modules that are a dependency of other modules we want to skip the validation of the config.
            // This config validation is performed in \Codeception\Module::__construct().
            // Explicitly setting $config to null skips this validation.
            $config = null;
        }

        $this->modules[$moduleName] = $module = $this->di->instantiate($moduleClass, [$this, $config], false);

        if ($this->moduleHasDependencies($module)) {
            $this->injectModuleDependencies($moduleName, $module);
        }

        // If module is not active its actions should not be included in the actor class
        $actions = $active ? $this->getActionsForModule($module, $config) : [];

        foreach ($actions as $action) {
            $this->actions[$action] = $moduleName;
        };

        return $module;
    }

    /**
     * Does a module have dependencies?
     *
     * @param \Codeception\Module $module
     * @return bool
     */
    private function moduleHasDependencies($module)
    {
        if (!$module instanceof DependsOnModule) {
            return false;
        }

        return (bool) $module->_depends();
    }

    /**
     * Get the actions of a module.
     *
     * @param \Codeception\Module $module
     * @param array $config
     * @return array
     */
    private function getActionsForModule($module, $config)
    {
        $reflectionClass = new \ReflectionClass($module);

        // Only public methods can be actions
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Should this module be loaded partially?
        $configuredParts = null;
        if ($module instanceof PartedModule && isset($config['part'])) {
            $configuredParts = is_array($config['part']) ? $config['part'] : [$config['part']];
        }

        $actions = [];
        foreach ($methods as $method) {
            if ($this->includeMethodAsAction($module, $method, $configuredParts)) {
                $actions[] = $method->name;
            }
        }

        return $actions;
    }

    /**
     * Should a method be included as an action?
     *
     * @param \Codeception\Module $module
     * @param \ReflectionMethod $method
     * @param array|null $configuredParts
     * @return bool
     */
    private function includeMethodAsAction($module, $method, $configuredParts = null)
    {
        // Filter out excluded actions
        if ($module::$excludeActions && in_array($method->name, $module::$excludeActions)) {
            return false;
        }

        // Keep only the $onlyActions if they are specified
        if ($module::$onlyActions && !in_array($method->name, $module::$onlyActions)) {
            return false;
        }

        // Do not include inherited actions if the static $includeInheritedActions property is set to false.
        // However, if an inherited action is also specified in the static $onlyActions property
        // it should be included as an action.
        if (!$module::$includeInheritedActions &&
            !in_array($method->name, $module::$onlyActions) &&
            $method->getDeclaringClass()->getName() != get_class($module)
        ) {
            return false;
        }

        // Do not include hidden methods, methods with a name starting with an underscore
        if (strpos($method->name, '_') === 0) {
            return false;
        };

        // If a part is configured for the module, only include actions from that part
        if ($configuredParts) {
            $moduleParts = Annotation::forMethod($module, $method->name)->fetchAll('part');
            if (!array_uintersect($moduleParts, $configuredParts, 'strcasecmp')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Is the module a helper?
     *
     * @param string $moduleName
     * @return bool
     */
    private function isHelper($moduleName)
    {
        return strpos($moduleName, '\\') !== false;
    }

    /**
     * Get the fully qualified class name for a module.
     *
     * @param string $moduleName
     * @return string
     */
    private function getModuleClass($moduleName)
    {
        if ($this->isHelper($moduleName)) {
            return $moduleName;
        }

        return self::MODULE_NAMESPACE . $moduleName;
    }

    /**
     * Is a module instantiated in this ModuleContainer?
     *
     * @param string $moduleName
     * @return bool
     */
    public function hasModule($moduleName)
    {
        return isset($this->modules[$moduleName]);
    }

    /**
     * Get a module from this ModuleContainer.
     *
     * @param string $moduleName
     * @return \Codeception\Module
     * @throws \Codeception\Exception\ModuleException
     */
    public function getModule($moduleName)
    {
        if (!$this->hasModule($moduleName)) {
            throw new ModuleException(__CLASS__, "Module $moduleName couldn't be connected");
        }

        return $this->modules[$moduleName];
    }

    /**
     * Get the module for an action.
     *
     * @param string $action
     * @return \Codeception\Module|null This method returns null if there is no module for $action
     */
    public function moduleForAction($action)
    {
        if (!isset($this->actions[$action])) {
            return null;
        }

        return $this->modules[$this->actions[$action]];
    }

    /**
     * Get all actions.
     *
     * @return array An array with actions as keys and module names as values.
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Get all modules.
     *
     * @return array An array with module names as keys and modules as values.
     */
    public function all()
    {
        return $this->modules;
    }

    /**
     * Mock a module in this ModuleContainer.
     *
     * @param string $moduleName
     * @param object $mock
     */
    public function mock($moduleName, $mock)
    {
        $this->modules[$moduleName] = $mock;
    }

    /**
     * Inject the dependencies of a module.
     *
     * @param string $moduleName
     * @param \Codeception\Lib\Interfaces\DependsOnModule $module
     * @throws \Codeception\Exception\ModuleException
     * @throws \Codeception\Exception\ModuleRequireException
     */
    private function injectModuleDependencies($moduleName, DependsOnModule $module)
    {
        $this->checkForMissingDependencies($moduleName, $module);

        if (!method_exists($module, '_inject')) {
            throw new ModuleException($module, 'Module requires method _inject to be defined to accept dependencies');
        }

        $dependencies = array_map(function ($dependency) {
            return $this->create($dependency, false);
        }, $this->getConfiguredDependencies($moduleName));

        call_user_func_array([$module, '_inject'], $dependencies);
    }

    /**
     * Check for missing dependencies.
     *
     * @param string $moduleName
     * @param \Codeception\Lib\Interfaces\DependsOnModule $module
     * @throws \Codeception\Exception\ModuleException
     * @throws \Codeception\Exception\ModuleRequireException
     */
    private function checkForMissingDependencies($moduleName, DependsOnModule $module)
    {
        $dependencies = $this->getModuleDependencies($module);
        $configuredDependenciesCount = count($this->getConfiguredDependencies($moduleName));

        if ($configuredDependenciesCount < count($dependencies)) {
            $missingDependency = array_keys($dependencies)[$configuredDependenciesCount];

            $message = sprintf(
                "\nThis module depends on %s\n\n\n%s",
                $missingDependency,
                $this->getErrorMessageForDependency($module, $missingDependency)
            );

            throw new ModuleRequireException($moduleName, $message);
        }
    }

    /**
     * Get the dependencies of a module.
     *
     * @param \Codeception\Lib\Interfaces\DependsOnModule $module
     * @return array
     * @throws \Codeception\Exception\ModuleException
     */
    private function getModuleDependencies(DependsOnModule $module)
    {
        $depends = $module->_depends();

        if (!$depends) {
            return [];
        }

        if (!is_array($depends)) {
            $message = sprintf("Method _depends of module '%s' must return an array", get_class($module));
            throw new ModuleException($module, $message);
        }

        return $depends;
    }

    /**
     * Get the configured dependencies for a module.
     *
     * @param string $moduleName
     * @return array
     */
    private function getConfiguredDependencies($moduleName)
    {
        $config = $this->getModuleConfig($moduleName);

        if (!isset($config['depends'])) {
            return [];
        }

        return is_array($config['depends']) ? $config['depends'] : [$config['depends']];
    }

    /**
     * Get the error message for a module dependency that is missing.
     *
     * @param \Codeception\Module $module
     * @param string $missingDependency
     * @return string
     */
    private function getErrorMessageForDependency($module, $missingDependency)
    {
        $depends = $module->_depends();

        return $depends[$missingDependency];
    }

    /**
     * Get the configuration for a module.
     *
     * A module with name $moduleName can be configured at two paths in a configuration file:
     * - modules.config.$moduleName
     * - modules.enabled.$moduleName
     *
     * This method checks both locations for configuration. If there is configuration at both locations
     * this method merges them, where the configuration at modules.enabled.$moduleName takes precedence
     * over modules.config.$moduleName if the same parameters are configured at both locations.
     *
     * @param string $moduleName
     * @return array
     */
    private function getModuleConfig($moduleName)
    {
        $config = isset($this->config['modules']['config'][$moduleName])
            ? $this->config['modules']['config'][$moduleName]
            : [];

        if (!isset($this->config['modules']['enabled'])) {
            return $config;
        }

        if (!is_array($this->config['modules']['enabled'])) {
            return $config;
        }

        foreach ($this->config['modules']['enabled'] as $enabledModuleConfig) {
            if (!is_array($enabledModuleConfig)) {
                continue;
            }

            $enabledModuleName = key($enabledModuleConfig);
            if ($enabledModuleName === $moduleName) {
                return Configuration::mergeConfigs(reset($enabledModuleConfig), $config);
            }
        }

        return $config;
    }

    /**
     * Check if there are conflicting modules in this ModuleContainer.
     *
     * @throws \Codeception\Exception\ModuleConflictException
     */
    public function validateConflicts()
    {
        $canConflict = [];
        foreach ($this->modules as $moduleName => $module) {
            $parted = $module instanceof PartedModule && $module->_getConfig('part');

            if ($this->active[$moduleName] && !$parted) {
                $canConflict[] = $module;
            }
        }

        foreach ($canConflict as $module) {
            foreach ($canConflict as $otherModule) {
                $this->validateConflict($module, $otherModule);
            }
        }
    }

    /**
     * Check if the modules passed as arguments to this method conflict with each other.
     *
     * @param \Codeception\Module $module
     * @param \Codeception\Module $otherModule
     * @throws \Codeception\Exception\ModuleConflictException
     */
    private function validateConflict($module, $otherModule)
    {
        if ($module === $otherModule || !$module instanceof ConflictsWithModule) {
            return;
        }

        $conflicts = $this->normalizeConflictSpecification($module->_conflicts());
        if ($otherModule instanceof $conflicts) {
            throw new ModuleConflictException($module, $otherModule);
        }
    }

    /**
     * Normalize the return value of ConflictsWithModule::_conflicts() to a class name.
     * This is necessary because it can return a module name instead of the name of a class or interface.
     *
     * @param string $conflicts
     * @return string
     */
    private function normalizeConflictSpecification($conflicts)
    {
        if (interface_exists($conflicts) || class_exists($conflicts)) {
            return $conflicts;
        }

        if ($this->hasModule($conflicts)) {
            return $this->getModule($conflicts);
        }

        return $conflicts;
    }
}
