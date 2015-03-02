<?php 
namespace Codeception\Lib;

use Codeception\Exception\Configuration as ConfigurationException;
use Codeception\Exception\Module as ModuleException;
use Codeception\Module;

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
    
    public function __construct(Di $di, $config)
    {
        $this->di = $di;
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
        $config = isset($this->config['modules']['config'][$moduleName])
            ? $this->config['modules']['config'][$moduleName]
            : [];

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
        $moduleClass = $namespace. self::MODULE_NAMESPACE . $moduleName;

        if (class_exists($moduleClass)) {
            return $this->instantiate($moduleName, $moduleClass, $config);
        }

        throw new ConfigurationException($moduleName.' could not be found and loaded');
    }

    public function has($module)
    {
        return isset($this->modules[$module]);
    }

    public function get($module)
    {
        if (!$this->has($module)) {
            throw new ModuleException(__CLASS__, "Module $module couldn't be connected");
        }
        return $this->modules[$module];
    }

    public function actions()
    {
        
    }

    private function instantiate($name, $class, $config)
    {
        $module = $this->di->instantiate($class, $config);
        $this->modules[$name] = $this->di->get($class);
        return $module;
        
    }
}