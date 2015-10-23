<?php
namespace Codeception\Lib\Connector;

use Illuminate\Database\Eloquent\Model;
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
    private $expectedEvents = [];

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
        $oldDb = null;
        if ($this->app['db'] && $this->app['db']->connection()) {
            $oldDb = $this->app['db'];
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

        // If events should be disabled mock the event dispatcher instance
        if ($this->module->config['disable_events']) {
            $this->mockEventDispatcher();
        }

        // Setup listener for expected events
        $this->listenForExpectedEvents();

        // Bootstrap the application
        $this->app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

        // Restore the old database object if available
        if ($oldDb) {
            $this->app['db'] = $oldDb;
            Model::setConnectionResolver($this->app['db']);
        }

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
     * Listen for expected events.
     * Even works when events are disabled.
     */
    private function listenForExpectedEvents()
    {
        if ($this->module->config['disable_events']) {
            // Events are disabled so we should listen for events through the mocked event dispatcher
            $mock = $this->app['events'];
            $callback = [$this, 'expectedEventListenerForMockedDispatcher'];

            $mock->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
                ->method('fire')
                ->will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($callback));
        } else {
            // Listen for all events by registering a wildcard event listener
            $this->app['events']->listen('*', [$this, 'expectedEventListenerForLaravelDispatcher']);
        }
    }

    /**
     * Add an expected event.
     *
     * @param string $event
     */
    public function addExpectedEvent($event)
    {
        $this->expectedEvents[] = $event;
    }

    /**
     * Returns all events that were expected but did not fire.
     *
     * @return array
     */
    public function missedEvents()
    {
        return $this->expectedEvents;
    }

    /**
     * Clear all expected events.
     * Should be called before each test.
     */
    public function clearExpectedEvents()
    {
        $this->expectedEvents = [];
    }

    /**
     * Wildcard event listener for the Laravel event dispatcher.
     * Used to check if expected events are fired.
     */
    public function expectedEventListenerForLaravelDispatcher()
    {
        $this->checkForExpectedEvent($this->app['events']->firing());
    }

    /**
     * If events are disabled the Laravel event dispatcher is replaced by a mock.
     * This method is called by the mocked fire() method to check for expected events.
     *
     * @param $event
     */
    public function expectedEventListenerForMockedDispatcher($event)
    {
        $this->checkForExpectedEvent($event);
    }

    /**
     * Check if the event was an expected event.
     *
     * @param $event
     */
    private function checkForExpectedEvent($event)
    {
        if (!$this->expectedEvents) {
            return;
        }

        if (is_object($event)) {
            $event = get_class($event);
        }

        // Events can be formatted as 'event.name: class'
        $segments = explode(':', $event);
        $event = $segments[0];

        foreach ($this->expectedEvents as $key => $expectedEvent) {
            if ($event == $expectedEvent || is_subclass_of($event, $expectedEvent)) {
                unset($this->expectedEvents[$key]);
            }
        }
    }

}