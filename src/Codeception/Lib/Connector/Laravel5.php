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

}