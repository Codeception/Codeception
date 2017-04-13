<?php
namespace Codeception\Lib\Connector\Shared;

/**
 * Common functions for Laravel family
 *
 * @package Codeception\Lib\Connector\Shared
 */
trait LaravelCommon
{
    /**
     * @var array
     */
    private $bindings = [];

    /**
     * @var array
     */
    private $contextualBindings = [];

    /**
     * @var array
     */
    private $instances = [];

    /**
     * @var array
     */
    private $applicationHandlers = [];

    /**
     * Apply the registered application handlers.
     */
    private function applyApplicationHandlers()
    {
        foreach ($this->applicationHandlers as $handler) {
            call_user_func($handler, $this->app);
        }
    }

    /**
     * Apply the registered Laravel service container bindings.
     */
    private function applyBindings()
    {
        foreach ($this->bindings as $abstract => $binding) {
            list($concrete, $shared) = $binding;

            $this->app->bind($abstract, $concrete, $shared);
        }
    }

    /**
     * Apply the registered Laravel service container contextual bindings.
     */
    private function applyContextualBindings()
    {
        foreach ($this->contextualBindings as $concrete => $bindings) {
            foreach ($bindings as $abstract => $implementation) {
                $this->app->addContextualBinding($concrete, $abstract, $implementation);
            }
        }
    }

    /**
     * Apply the registered Laravel service container instance bindings.
     */
    private function applyInstances()
    {
        foreach ($this->instances as $abstract => $instance) {
            $this->app->instance($abstract, $instance);
        }

    }

    //======================================================================
    // Public methods called by module
    //======================================================================

    /**
     * Register a Laravel service container binding that should be applied
     * after initializing the Laravel Application object.
     *
     * @param $abstract
     * @param $concrete
     * @param bool $shared
     */
    public function haveBinding($abstract, $concrete, $shared = false)
    {
        $this->bindings[$abstract] = [$concrete, $shared];
    }

    /**
     * Register a Laravel service container contextual binding that should be applied
     * after initializing the Laravel Application object.
     *
     * @param $concrete
     * @param $abstract
     * @param $implementation
     */
    public function haveContextualBinding($concrete, $abstract, $implementation)
    {
        if (! isset($this->contextualBindings[$concrete])) {
            $this->contextualBindings[$concrete] = [];
        }

        $this->contextualBindings[$concrete][$abstract] = $implementation;
    }

    /**
     * Register a Laravel service container instance binding that should be applied
     * after initializing the Laravel Application object.
     *
     * @param $abstract
     * @param $instance
     */
    public function haveInstance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Register a handler than can be used to modify the Laravel application object after it is initialized.
     * The Laravel application object will be passed as an argument to the handler.
     *
     * @param $handler
     */
    public function haveApplicationHandler($handler)
    {
        $this->applicationHandlers[] = $handler;
    }

    /**
     * Clear the registered application handlers.
     */
    public function clearApplicationHandlers()
    {
        $this->applicationHandlers = [];
    }
}
