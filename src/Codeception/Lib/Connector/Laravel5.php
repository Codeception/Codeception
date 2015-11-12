<?php
namespace Codeception\Lib\Connector;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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
     * @var array
     */
    private $triggeredEvents = [];

    /**
     * @var object
     */
    private $oldDb;

    /**
     * Constructor.
     * @param \Codeception\Module\Laravel5 $module
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->initialize();

        $components = parse_url($this->app['config']->get('app.url', 'http://localhost'));
        $host = isset($components['host']) ? $components['host'] : 'localhost';

        parent::__construct($this->app, ['HTTP_HOST' => $host]);

        // Parent constructor defaults to not following redirects
        $this->followRedirects(true);
    }

    /**
     * @param SymfonyRequest $request
     * @return Response
     */
    protected function doRequest($request)
    {
        $this->initialize($request);

        $request = Request::createFromBase($request);
        $response = $this->kernel->handle($request);
        $this->app->make('Illuminate\Contracts\Http\Kernel')->terminate($request, $response);

        return $response;
    }

    /**
     * Initialize the Laravel framework.
     * @param SymfonyRequest $request
     */
    private function initialize($request = null)
    {
        // Store a reference to the database object
        // so the database connection can be reused during tests
        $this->oldDb = null;
        if ($this->app['db'] && $this->app['db']->connection()) {
            $this->oldDb = $this->app['db'];
        }

        // The module can login a user with the $I->amLoggedAs() method,
        // but this is not persisted between requests. Store a reference
        // to the logged in user to simulate this.
        $loggedInUser = null;
        if ($this->app['auth'] && $this->app['auth']->check()) {
            $loggedInUser = $this->app['auth']->user();
        }

        // Load the application object
        $this->app = $this->kernel = $this->loadApplication();

        // Set the request instance for the application
        if (is_null($request)) {
            $appConfig = require $this->module->config['project_dir'] . 'config/app.php';
            $request = SymfonyRequest::create($appConfig['url']);
        }

        $this->app->instance('request', Request::createFromBase($request));
        $this->app->instance('middleware.disable', $this->module->config['disable_middleware']);

        // Reset the old database after the DatabaseServiceProvider ran.
        // This way other service providers that rely on the $app['db'] entry
        // have the correct instance available.
        if ($this->oldDb) {
            $this->app['events']->listen('Illuminate\Database\DatabaseServiceProvider', function () {
                $this->app->singleton('db', function () {
                    return $this->oldDb;
                });
            });
        }

        $this->app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

        // If events should be disabled mock the event dispatcher instance
        if ($this->module->config['disable_events']) {
            $this->mockEventDispatcher();
        }

        // Setup an event listener to listen for all events that are triggered
        $this->setupEventListener();

        // If there was a user logged in restore this user.
        // Also reload the user object from the user provider to prevent stale user data.
        if ($loggedInUser) {
            $refreshed = $this->app['auth']->getProvider()->retrieveById($loggedInUser->getAuthIdentifier());
            $this->app['auth']->setUser($refreshed ?: $loggedInUser);
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
        $this->app->instance('events', $mock);
    }

    /**
     * Listen for events.
     * Even works when events are disabled.
     */
    private function setupEventListener()
    {
        if ($this->module->config['disable_events']) {
            // Events are disabled so we should listen for events through the mocked event dispatcher
            $mock = $this->app['events'];
            $callback = function ($event) {
                $this->triggeredEvents[] = $this->normalizeEvent($event);
            };

            $mock->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
                ->method('fire')
                ->will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($callback));
        } else {
            // Listen for all events by registering a wildcard event listener
            $callback = function () {
                $this->triggeredEvents[] = $this->normalizeEvent($this->app['events']->firing());
            };
            $this->app['events']->listen('*', $callback);
        }
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
     * Clear all expected events.
     * Should be called before each test.
     */
    public function clearTriggeredEvents()
    {
        $this->triggeredEvents = [];
    }

}