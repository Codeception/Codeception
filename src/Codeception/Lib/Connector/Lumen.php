<?php
namespace Codeception\Lib\Connector;

use Codeception\Lib\Connector\Lumen\DummyKernel;
use Codeception\Lib\Connector\Shared\LaravelCommon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use Illuminate\Http\UploadedFile;

class Lumen extends Client
{
    use LaravelCommon;

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

        $this->applyBindings();
        $this->applyContextualBindings();
        $this->applyInstances();
        $this->applyApplicationHandlers();

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

        if (class_exists(Facade::class)) {
            // If the container has been instantiated ever,
            // we need to clear its static fields before create new container.
            Facade::clearResolvedInstances();
        }

        $this->app = $this->kernel = require $this->module->config['bootstrap_file'];
        
        // in version 5.6.*, lumen introduced the same design pattern like Laravel
        // to load all service provider we need to call on Laravel\Lumen\Application::boot()
        if (method_exists($this->app, 'boot')) {
            $this->app->boot();
        }
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
}
