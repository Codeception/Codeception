<?php
namespace Codeception\Lib\Connector;

use Codeception\Lib\Connector\Lumen\DummyKernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class Lumen extends Client
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    /**
     * @var \Codeception\Module\Lumen
     */
    private $module;

    /**
     * @var bool
     */
    private $firstRequest = true;

    /**
     * @var object
     */
    private $oldDb;

    /**
     * Constructor.
     *
     * @param \Codeception\Module\Lumen $module
     */
    public function __construct($module)
    {
        $this->module = $module;

        $components = parse_url($this->module->config['url']);
        $server = ['HTTP_HOST' => $components['host']];

        // Pass a DummyKernel to satisfy the arguments of the parent constructor.
        // The actual kernel object is set in the initialize() method.
        parent::__construct(new DummyKernel(), $server);

        // Parent constructor defaults to not following redirects
        $this->followRedirects(true);

        $this->initialize();
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

        $request = Request::createFromBase($request);
        $response = $this->kernel->handle($request);

        $method = new \ReflectionMethod(get_class($this->app), 'callTerminableMiddleware');
        $method->setAccessible(true);
        $method->invoke($this->app, $response);

        return $response;
    }

    /**
     * Initialize the Lumen framework.
     *
     * @param SymfonyRequest|null $request
     */
    private function initialize($request = null)
    {
        // Store a reference to the database object
        // so the database connection can be reused during tests
        $this->oldDb = null;
        if (isset($this->app['db']) && $this->app['db']->connection()) {
            $this->oldDb = $this->app['db'];
        }

        $this->app = $this->kernel = require $this->module->config['bootstrap_file'];

        Facade::clearResolvedInstances();

        // Lumen registers necessary bindings on demand when calling $app->make(),
        // so here we force the request binding before registering our own request object,
        // otherwise Lumen will overwrite our request object.
        $this->app->make('request');

        $request = $request ?: SymfonyRequest::create($this->module->config['url']);
        $this->app->instance('Illuminate\Http\Request', Request::createFromBase($request));

        // Reset the old database if there is one
        if ($this->oldDb) {
            $this->app->singleton('db', function () {
                return $this->oldDb;
            });
            Model::setConnectionResolver($this->oldDb);
        }

        $this->module->setApplication($this->app);
    }
}
