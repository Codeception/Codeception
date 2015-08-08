<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException as ConfigurationException;
use Codeception\Exception\ModuleException as ModuleException;
use Codeception\Exception\ModuleConflictException as ModuleConflictException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Module;
use Codeception\Util\Annotation;

class ModuleContainer
{
    const MODULE_NAMESPACE = '\\Codeception\\Module\\';

    protected $config;

    /**
     * @var Di
     */
    protected $di;

    protected $modules = [];
    protected $active = [];
    protected $actions = [];

    public function __construct(Di $di, $config)
    {
        $this->di = $di;
        $this->di->set($this);
        $this->config = $config;
    }

    /**
     * @param $moduleName
     * @param bool $active
     * @throws ConfigurationException
     * @return Module
     */
    public function create($moduleName, $active = true)
    {
        $this->active[$moduleName] = $active;
        $config = $this->getModuleConfig($moduleName);

        // skip config validation on dependent module
        if (empty($config) && !$active) {
            $config = null;
        }

        // helper
        $hasNamespace = (mb_strpos($moduleName, '\\') !== false);
        if ($hasNamespace) {
            return $this->instantiate($moduleName, $moduleName, $config);
        }

        // standard module
        $moduleClass = self::MODULE_NAMESPACE . $moduleName;
        if (class_exists($moduleClass)) {
            return $this->instantiate($moduleName, $moduleClass, $config);
        }

        // (deprecated) try find module under namespace setting
        $namespace = isset($this->config['namespace']) ? $this->config['namespace'] : '';
        $moduleClass = $namespace . self::MODULE_NAMESPACE . $moduleName;

        if (class_exists($moduleClass)) {
            return $this->instantiate($moduleName, $moduleClass, $config);
        }

        throw new ConfigurationException("Module $moduleName could not be found and loaded");
    }

    public function hasModule($module)
    {
        return isset($this->modules[$module]);
    }

    public function getModule($module)
    {
        if (!$this->hasModule($module)) {
            throw new ModuleException(__CLASS__, "Module $module couldn't be connected");
        }
        return $this->modules[$module];
    }

    public function moduleForAction($action)
    {
        if (!isset($this->actions[$action])) {
            return null;
        }
        return $this->modules[$this->actions[$action]];
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function all()
    {
        return $this->modules;
    }

    private function instantiate($name, $class, $config)
    {
        $module = $this->di->instantiate($class, [$this, $config], false);
        $this->modules[$name] = $module;

        if (!$this->active[$name]) {
            // if module is not active, its actions should not be included into actor class
            return $module;
        }
        
        if ($module instanceof DependsOnModule) {
            $this->injectDependentModule($name, $module);
        }

        $class = new \ReflectionClass($module);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $inherit = $class->getStaticPropertyValue('includeInheritedActions');
            $only = $class->getStaticPropertyValue('onlyActions');
            $exclude = $class->getStaticPropertyValue('excludeActions');

            // exclude methods when they are listed as excluded
            if (in_array($method->name, $exclude)) {
                continue;
            }

            if (!empty($only)) {
                // skip if method is not listed
                if (!in_array($method->name, $only)) {
                    continue;
                }
            } else {
                // skip if method is inherited and inheritActions == false
                if (!$inherit && $method->getDeclaringClass() != $class) {
                    continue;
                }
            }
            // those with underscore at the beginning are considered as hidden
            if (strpos($method->name, '_') === 0) {
                continue;
            }

            if ($module instanceof PartedModule && isset($config['part'])) {
                if (!$this->moduleActionBelongsToPart($module, $method->name, $config['part'])) {
                    continue;
                }
            }

            $this->actions[$method->name] = $name;
        }
        return $module;
    }

    public function injectDependentModule($name, DependsOnModule $module)
    {
        $message = '';
        $dependency = $module->_depends();
        if (empty($dependency)) {
            return;
        }
        if (is_array($dependency)) {
            $message = reset($dependency);
            $dependency = key($dependency);
        }
        $config = $this->getModuleConfig($name);
        if (!isset($config['depends'])) {
            throw new ModuleRequireException($module,
                "\nThis module depends on $dependency\n" .
                "\n \n$message");
        }
        $dependentModule = $this->create($config['depends'], false);
        if (!method_exists($module, '_inject')) {
            throw new ModuleException($module, 'Module requires method _inject to be defined to accept dependencies');
        }
        $module->_inject($dependentModule);
        $dependentModule->_setConfig([]);
    }

    public function validateConflicts()
    {
        $moduleNames = array_keys($this->modules);
        for ($i = 0; $i < count($this->modules); $i++) {
            /** @var $currentModule Module  **/
            $currentModule = $this->modules[$moduleNames[$i]];
            if (!$currentModule instanceof ConflictsWithModule) {
                continue;
            }
            for ($j = $i; $j < count($this->modules); $j++) {
                $inspectedModule = $this->modules[$moduleNames[$j]];
                $nameAndInterfaces = array_merge([get_class($inspectedModule), $inspectedModule->_getName()], class_implements($inspectedModule));
                if (in_array(ltrim($currentModule->_conflicts(), '\\'), $nameAndInterfaces)) {
                    throw new ModuleConflictException($currentModule, $inspectedModule);
                }
            }
        }
    }

    protected function moduleActionBelongsToPart($module, $action, $part)
    {
        if (!is_array($part)) {
            $part = [strtolower($part)];
        }
        $part = array_map('strtolower', $part);
        $parts = Annotation::forMethod($module, $action)->fetchAll('part');
        $usedParts = array_intersect($parts, $part);
        return !empty($usedParts);
    }

    protected function getModuleConfig($module)
    {
        // get config for all modules
        $config = isset($this->config['modules']['config'][$module])
            ? $this->config['modules']['config'][$module]
            : [];

        if (!isset($this->config['modules']['enabled'])) {
            return $config;
        }

        if (!is_array($this->config['modules']['enabled'])) {
            return $config;
        }


        // get config for enabled modules
        foreach ($this->config['modules']['enabled'] as $enabledModuleConfig) {
            if (!is_array($enabledModuleConfig)) {
                continue;
            }
            $enabledModuleName = key($enabledModuleConfig);
            if ($enabledModuleName !== $module) {
                continue;
            }
            $config = Configuration::mergeConfigs(reset($enabledModuleConfig), $config);
        }
        return $config;
    }
}