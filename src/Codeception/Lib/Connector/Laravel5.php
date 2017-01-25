<?php
namespace Codeception\Lib\Connector;

use Codeception\Lib\Connector\Laravel5\ExceptionHandlerDecorator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class Laravel5 extends Client
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var \Codeception\Module\Laravel5
     */
    private $module;

    /**
     * @var bool
     */
    private $firstRequest = true;

    /**
     * @var array
     */
    private $triggeredEvents = [];

    /**
     * @var bool
     */
    private $exceptionHandlingDisabled;

    /**
     * @var bool
     */
    private $middlewareDisabled;

    /**
     * @var bool
     */
    private $eventsDisabled;

    /**
     * @var bool
     */
    private $modelEventsDisabled;

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
     * @var object
     */
    private $oldDb;

    /**
     * Constructor.
     *
     * @param \Codeception\Module\Laravel5 $module
     */
    public function __construct($module)
    {
        $this->module = $module;

        $this->exceptionHandlingDisabled = $this->module->config['disable_exception_handling'];
        $this->middlewareDisabled = $this->module->config['disable_middleware'];
        $this->eventsDisabled = $this->module->config['disable_events'];
        $this->modelEventsDisabled = $this->module->config['disable_model_events'];

        $this->initialize();

        $components = parse_url($this->app['config']->get('app.url', 'http://localhost'));
        if (array_key_exists('url', $this->module->config)) {
            $components = parse_url($this->module->config['url']);
        }
        $host = isset($components['host']) ? $components['host'] : 'localhost';

        parent::__construct($this->app, ['HTTP_HOST' => $host]);

        // Parent constructor defaults to not following redirects
        $this->followRedirects(true);
    }

    /**
     * Execute a request.
     *
     * @param SymfonyRequest $request
     * @return Response
     */
    protected function doRequest($request)
    {
        if (!$this->firstRequest) {
            $this->initialize($request);
        }
        $this->firstRequest = false;

        $this->applyBindings();
        $this->applyContextualBindings();
        $this->applyInstances();

        $request = Request::createFromBase($request);
        $response = $this->kernel->handle($request);
        $this->app->make('Illuminate\Contracts\Http\Kernel')->terminate($request, $response);

        return $response;
    }

    /**
     * Make sure files are \Illuminate\Http\UploadedFile instances with the private $test property set to true.
     * Fixes issue https://github.com/Codeception/Codeception/pull/3417.
     *
     * @param array $files
     * @return array
     */
    protected function filterFiles(array $files)
    {
        $files = parent::filterFiles($files);

        if (! class_exists('Illuminate\Http\UploadedFile')) {
            // The \Illuminate\Http\UploadedFile class was introduced in Laravel 5.2.15,
            // so don't change the $files array if it does not exist.
            return $files;
        }

        return $this->convertToTestFiles($files);
    }

    /**
     * @param array $files
     * @return array
     */
    private function convertToTestFiles(array $files)
    {
        $filtered = [];

        foreach ($files as $key => $value) {
            if (is_array($value)) {
                $filtered[$key] = $this->convertToTestFiles($value);
            } else {
                $filtered[$key] = UploadedFile::createFromBase($value, true);
            }
        }

        return $filtered;
    }

    /**
     * Initialize the Laravel framework.
     *
     * @param SymfonyRequest $request
     */
    private function initialize($request = null)
    {
        // Store a reference to the database object
        // so the database connection can be reused during tests
        $this->oldDb = null;
        if (isset($this->app['db']) && $this->app['db']->connection()) {
            $this->oldDb = $this->app['db'];
        }

        $this->app = $this->kernel = $this->loadApplication();

        // Set the request instance for the application,
        if (is_null($request)) {
            $appConfig = require $this->module->config['project_dir'] . 'config/app.php';
            $request = SymfonyRequest::create($appConfig['url']);
        }
        $this->app->instance('request', Request::createFromBase($request));

        // Reset the old database after all the service providers are registered.
        if ($this->oldDb) {
            $this->app['events']->listen('bootstrapped: Illuminate\Foundation\Bootstrap\RegisterProviders', function () {
                $this->app->singleton('db', function () {
                    return $this->oldDb;
                });
            });
        }

        $this->app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

        // Record all triggered events by adding a wildcard event listener
        // Since Laravel 5.4 wildcard event handlers receive the event name as the first argument,
        // but for earlier Laravel versions the firing() method of the event dispatcher should be used
        // to determine the event name.
        if (method_exists($this->app['events'], 'firing')) {
            $listener = function () {
                $this->triggeredEvents[] = $this->normalizeEvent($this->app['events']->firing());
            };
        } else {
            $listener = function ($event) {
                $this->triggeredEvents[] = $this->normalizeEvent($event);
            };
        }
        $this->app['events']->listen('*', $listener);

        // Replace the Laravel exception handler with our decorated exception handler,
        // so exceptions can be intercepted for the disable_exception_handling functionality.
        $decorator = new ExceptionHandlerDecorator($this->app['Illuminate\Contracts\Debug\ExceptionHandler']);
        $decorator->exceptionHandlingDisabled($this->exceptionHandlingDisabled);
        $this->app->instance('Illuminate\Contracts\Debug\ExceptionHandler', $decorator);

        if ($this->module->config['disable_middleware'] || $this->middlewareDisabled) {
            $this->app->instance('middleware.disable', true);
        }

        if ($this->module->config['disable_events'] || $this->eventsDisabled) {
            $this->mockEventDispatcher();
        }

        if ($this->module->config['disable_model_events'] || $this->modelEventsDisabled) {
            Model::unsetEventDispatcher();
        }

        $this->module->setApplication($this->app);
    }

    /**
     * Boot the Laravel application object.
     * @return Application
     * @throws ModuleConfig
     */
    private function loadApplication()
    {
        $app = require $this->module->config['bootstrap_file'];
        $app->loadEnvironmentFrom($this->module->config['environment_file']);
        $app->instance('request', new Request());

        return $app;
    }

    /**
     * Replace the Laravel event dispatcher with a mock.
     */
    private function mockEventDispatcher()
    {
        $mockGenerator = new \PHPUnit_Framework_MockObject_Generator;
        $mock = $mockGenerator->getMock('Illuminate\Contracts\Events\Dispatcher');

        // Even if events are disabled we still want to record the triggered events.
        // But by mocking the event dispatcher the wildcard listener registered in the initialize method is removed.
        // So to record the triggered events we have to catch the calls to the fire method of the event dispatcher mock.
        $callback = function ($event) {
            $this->triggeredEvents[] = $this->normalizeEvent($event);

            return [];
        };

        // In Laravel 5.4 the Illuminate\Contracts\Events\Dispatcher interface was changed,
        // the 'fire' method was renamed to 'dispatch'. This code determines the correct method to mock.
        $method = method_exists($this->app['events'], 'dispatch') ? 'dispatch' : 'fire';

        $mock->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
            ->method($method)
            ->will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($callback));

        $this->app->instance('events', $mock);
    }

    /**
     * Normalize events to class names.
     *
     * @param $event
     * @return string
     */
    private function normalizeEvent($event)
    {
        if (is_object($event)) {
            $event = get_class($event);
        }

        if (preg_match('/^bootstrapp(ing|ed): /', $event)) {
            return $event;
        }

        // Events can be formatted as 'event.name: parameters'
        $segments = explode(':', $event);

        return $segments[0];
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
     * Did an event trigger?
     *
     * @param $event
     * @return bool
     */
    public function eventTriggered($event)
    {
        $event = $this->normalizeEvent($event);

        foreach ($this->triggeredEvents as $triggeredEvent) {
            if ($event == $triggeredEvent || is_subclass_of($event, $triggeredEvent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Disable Laravel exception handling.
     */
    public function disableExceptionHandling()
    {
        $this->exceptionHandlingDisabled = true;
        $this->app['Illuminate\Contracts\Debug\ExceptionHandler']->exceptionHandlingDisabled(true);
    }

    /**
     * Enable Laravel exception handling.
     */
    public function enableExceptionHandling()
    {
        $this->exceptionHandlingDisabled = false;
        $this->app['Illuminate\Contracts\Debug\ExceptionHandler']->exceptionHandlingDisabled(false);
    }

    /**
     * Disable events.
     */
    public function disableEvents()
    {
        $this->eventsDisabled = true;
        $this->mockEventDispatcher();
    }

    /**
     * Disable model events.
     */
    public function disableModelEvents()
    {
        $this->modelEventsDisabled = true;
        Model::unsetEventDispatcher();
    }

    /*
     * Disable middleware.
     */
    public function disableMiddleware()
    {
        $this->middlewareDisabled = true;
        $this->app->instance('middleware.disable', true);
    }

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
}
