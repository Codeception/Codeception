<?php

declare(strict_types=1);

namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\InjectionException;
use Codeception\Exception\ModuleConflictException;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Module;
use Codeception\Util\Annotation;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Class ModuleContainer
 * @package Codeception\Lib
 */
class ModuleContainer
{
    /**
     * @var string
     */
    public const MODULE_NAMESPACE = '\\Codeception\\Module\\';

    /**
     * @var int
     */
    public const MAXIMUM_LEVENSHTEIN_DISTANCE = 5;

    /**
     * @var array<string, string>
     */
    public static array $packages = [
        'AMQP' => 'codeception/module-amqp',
        'Apc' => 'codeception/module-apc',
        'Asserts' => 'codeception/module-asserts',
        'Cli' => 'codeception/module-cli',
        'DataFactory' => 'codeception/module-datafactory',
        'Db' => 'codeception/module-db',
        'Doctrine' => "codeception/module-doctrine",
        'Filesystem' => 'codeception/module-filesystem',
        'FTP' => 'codeception/module-ftp',
        'Laravel' => 'codeception/module-laravel',
        'Lumen' => 'codeception/module-lumen',
        'Memcache' => 'codeception/module-memcache',
        'MongoDb' => 'codeception/module-mongodb',
        'Phalcon' => 'codeception/module-phalcon',
        'PhpBrowser' => 'codeception/module-phpbrowser',
        'Queue' => 'codeception/module-queue',
        'Redis' => 'codeception/module-redis',
        'REST' => 'codeception/module-rest',
        'Sequence' => 'codeception/module-sequence',
        'SOAP' => 'codeception/module-soap',
        'Symfony' => 'codeception/module-symfony',
        'WebDriver' => "codeception/module-webdriver",
        'Yii2' => "codeception/module-yii2",
        'ZendExpressive' => 'codeception/module-zendexpressive',
        'ZF2' => 'codeception/module-zf2',
    ];

    /**
     * @var array<string,Module>
     */
    private array $modules = [];

    private array $active = [];

    private array $actions = [];

    public function __construct(private readonly Di $di, private array $config)
    {
        $this->di->set($this);
    }

    /**
     * Create a module.
     *
     * @throws ConfigurationException
     * @throws InjectionException
     * @throws ModuleException
     * @throws ModuleRequireException
     * @throws ReflectionException
     */
    public function create(string $moduleName, bool $active = true): ?object
    {
        $this->active[$moduleName] = $active;

        $moduleClass = $this->getModuleClass($moduleName);
        if (!class_exists($moduleClass)) {
            if (isset(self::$packages[$moduleName])) {
                $package = self::$packages[$moduleName];
                throw new ConfigurationException("Codeception's module {$moduleName} not found. Install it with:\n\ncomposer require {$package} --dev");
            }
            throw new ConfigurationException("Module {$moduleName} could not be found and loaded");
        }

        $config = $this->getModuleConfig($moduleName);

        if ($config === [] && !$active) {
            // For modules that are a dependency of other modules we want to skip the validation of the config.
            // This config validation is performed in \Codeception\Module::__construct().
            // Explicitly setting $config to null skips this validation.
            $config = null;
        }
        $this->modules[$moduleName] = $this->di->instantiate($moduleClass, [$this, $config], 'false');

        $module = $this->modules[$moduleName];

        if ($this->moduleHasDependencies($module)) {
            $this->injectModuleDependencies($moduleName, $module);
        }

        // If module is not active its actions should not be included in the actor class
        $actions = $active ? $this->getActionsForModule($module, $config) : [];

        foreach ($actions as $action) {
            $this->actions[$action] = $moduleName;
        }

        return $module;
    }

    /**
     * Does a module have dependencies?
     */
    private function moduleHasDependencies(Module $module): bool
    {
        if (!$module instanceof DependsOnModule) {
            return false;
        }

        return (bool)$module->_depends();
    }

    /**
     * Get the actions of a module.
     *
     * @return string[]
     */
    private function getActionsForModule(Module $module, array $config): array
    {
        $reflectionClass = new ReflectionClass($module);

        // Only public methods can be actions
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

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
     */
    private function includeMethodAsAction(Module $module, ReflectionMethod $method, ?array $configuredParts = null): bool
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
        if (
            !$module::$includeInheritedActions &&
            !in_array($method->name, $module::$onlyActions) &&
            $method->getDeclaringClass()->getName() != $module::class
        ) {
            return false;
        }

        // Do not include hidden methods, methods with a name starting with an underscore
        if (str_starts_with($method->name, '_')) {
            return false;
        }

        // If a part is configured for the module, only include actions from that part
        if ($configuredParts) {
            $moduleParts = Annotation::forMethod($module, $method->name)->fetchAll('part');
            if (array_uintersect($moduleParts, $configuredParts, 'strcasecmp') === []) {
                return false;
            }
        }

        return true;
    }

    /**
     * Is the module a helper?
     */
    private function isHelper(string $moduleName): bool
    {
        return str_contains($moduleName, '\\');
    }

    /**
     * Get the fully qualified class name for a module.
     */
    private function getModuleClass(string $moduleName): string
    {
        if ($this->isHelper($moduleName)) {
            return $moduleName;
        }

        return self::MODULE_NAMESPACE . $moduleName;
    }

    /**
     * Is a module instantiated in this ModuleContainer?
     */
    public function hasModule(string $moduleName): bool
    {
        return isset($this->modules[$moduleName]);
    }

    /**
     * Get a module from this ModuleContainer.
     *
     * @throws ModuleException
     */
    public function getModule(string $moduleName): Module
    {
        if (!$this->hasModule($moduleName)) {
            $this->throwMissingModuleExceptionWithSuggestion(self::class, $moduleName);
        }

        return $this->modules[$moduleName];
    }

    public function throwMissingModuleExceptionWithSuggestion(string $className, string $moduleName): void
    {
        $suggestedModuleNameInfo = $this->getModuleSuggestion($moduleName);
        throw new ModuleException($className, "Module {$moduleName} couldn't be connected" . $suggestedModuleNameInfo);
    }

    protected function getModuleSuggestion(string $missingModuleName): string
    {
        $shortestLevenshteinDistance = null;
        $suggestedModuleName = null;
        foreach (array_keys($this->modules) as $moduleName) {
            $levenshteinDistance = levenshtein($missingModuleName, $moduleName);
            if ($shortestLevenshteinDistance === null || $levenshteinDistance <= $shortestLevenshteinDistance) {
                $shortestLevenshteinDistance = $levenshteinDistance;
                $suggestedModuleName = $moduleName;
            }
        }

        if ($suggestedModuleName !== null && $shortestLevenshteinDistance <= self::MAXIMUM_LEVENSHTEIN_DISTANCE) {
            return " (did you mean '{$suggestedModuleName}'?)";
        }

        return '';
    }

    /**
     * Get the module for an action.
     *
     * @return Module|null
     */
    public function moduleForAction(string $action)
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
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Get all modules.
     *
     * @return array An array with module names as keys and modules as values.
     */
    public function all(): array
    {
        return $this->modules;
    }

    /**
     * Mock a module in this ModuleContainer.
     */
    public function mock(string $moduleName, object $mock): void
    {
        $this->modules[$moduleName] = $mock;
    }

    /**
     * Inject the dependencies of a module.
     *
     * @throws ModuleException
     * @throws ModuleRequireException
     */
    private function injectModuleDependencies(string $moduleName, DependsOnModule $module): void
    {
        $this->checkForMissingDependencies($moduleName, $module);

        if (!method_exists($module, '_inject')) {
            throw new ModuleException($module, 'Module requires method _inject to be defined to accept dependencies');
        }

        $dependencies = array_map(fn($dependency): ?object => $this->create($dependency, false), $this->getConfiguredDependencies($moduleName));

        call_user_func_array([$module, '_inject'], $dependencies);
    }

    /**
     * Check for missing dependencies.
     *
     * @throws ModuleException|ModuleRequireException
     */
    private function checkForMissingDependencies(string $moduleName, DependsOnModule $module): void
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
     * @throws ModuleException
     */
    private function getModuleDependencies(DependsOnModule $module): array
    {
        $depends = $module->_depends();

        if ($depends === []) {
            return [];
        }

        if (!is_array($depends)) {
            $message = sprintf("Method _depends of module '%s' must return an array", $module::class);
            throw new ModuleException($module, $message);
        }

        return $depends;
    }

    /**
     * Get the configured dependencies for a module.
     */
    private function getConfiguredDependencies(string $moduleName): array
    {
        $config = $this->getModuleConfig($moduleName);

        if (!isset($config['depends'])) {
            return [];
        }

        return is_array($config['depends']) ? $config['depends'] : [$config['depends']];
    }

    /**
     * Get the error message for a module dependency that is missing.
     */
    private function getErrorMessageForDependency(DependsOnModule $module, string $missingDependency): string
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
     */
    private function getModuleConfig(string $moduleName): array
    {
        $config = $this->config['modules']['config'][$moduleName] ?? [];

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
                $moduleConfig = reset($enabledModuleConfig);
                if (!is_array($moduleConfig)) {
                    return $config;
                }
                return Configuration::mergeConfigs($moduleConfig, $config);
            }
        }

        return $config;
    }

    /**
     * Check if there are conflicting modules in this ModuleContainer.
     *
     * @throws ModuleConflictException
     */
    public function validateConflicts(): void
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
     * @throws ModuleConflictException
     */
    private function validateConflict(Module $module, Module $otherModule): void
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
     * @return class-string|Module|string
     */
    private function normalizeConflictSpecification(string $conflicts): string|Module
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
