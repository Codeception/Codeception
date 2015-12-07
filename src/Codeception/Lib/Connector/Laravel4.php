<?php
namespace Codeception\Lib\Connector;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class Laravel4 extends Client
{

    /**
     * @var \Illuminate\Foundation\Application
     */
    private $app;

    /**
     * @var \Codeception\Module\Laravel4
     */
    private $module;

    /**
     * @var bool
     */
    private $firstRequest = true;

    /**
     * Constructor.
     *
     * @param \Codeception\Module\Laravel4 $module
     */
    public function  __construct($module)
    {
        $this->module = $module;
        $this->initialize();

        $components = parse_url($this->app['config']->get('app.url', 'http://localhost'));
        $host = isset($components['host']) ? $components['host'] : 'localhost';

        parent::__construct($this->kernel, ['HTTP_HOST' => $host]);

        // Parent constructor defaults to not following redirects
        $this->followRedirects(true);
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function doRequest($request)
    {
        if (!$this->firstRequest) {
            $this->initialize($request);
        }
        $this->firstRequest = false;

        $response = $this->kernel->handle($request);
        $this->kernel->terminate($request, $response);

        return $response;
    }

    /**
     * Initialize the Laravel Framework.
     *
     * @throws ModuleConfig
     */
    private function initialize()
    {
        // Store a reference to the database object
        // so the database connection can be reused during tests
        $oldDb = null;
        if ($this->app['db'] && $this->app['db']->connection()) {
            $oldDb = $this->app['db'];
        }

        // Store the current value for the router filters
        // so it can be reset after reloading the application
        $oldFiltersEnabled = null;
        if ($router = $this->app['router']) {
            $property = new \ReflectionProperty(get_class($router), 'filtering');
            $property->setAccessible(true);
            $oldFiltersEnabled = $property->getValue($router);
        }

        $this->app = $this->loadApplication();
        $this->kernel = $this->getStackedClient();
        $this->app->boot();

        // Reset the booted flag of the Application object
        // so the app will be booted again if it receives a new Request
        $property = new \ReflectionProperty(get_class($this->app), 'booted');
        $property->setAccessible(true);
        $property->setValue($this->app, false);

        if ($oldDb) {
            $this->app['db'] = $oldDb;
            Model::setConnectionResolver($this->app['db']);
        }

        if (!is_null($oldFiltersEnabled)) {
            $oldFiltersEnabled ? $this->app['router']->enableFilters() : $this->app['router']->disableFilters();
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
        // The following two variables are used in the Illuminate/Foundation/start.php file
        // which is included in the bootstrap start file.
        $unitTesting = $this->module->config['unit'];
        $testEnvironment = $this->module->config['environment'];

        $app = require $this->module->config['start_file'];
        $this->setConfiguredSessionDriver($app);

        return $app;
    }

    /**
     * Get the configured session driver.
     * Laravel 4 forces the array session driver if the application is run from the console.
     * This happens in \Illuminate\Session\SessionServiceProvider::setupDefaultDriver() method.
     * This method is used to set the correct session driver that is configured in the config files.
     *
     * @param Application $app
     */
    private function setConfiguredSessionDriver(Application $app)
    {
        $configDir = $app['path'] . DIRECTORY_SEPARATOR . 'config';
        $configFiles = array(
            $configDir . DIRECTORY_SEPARATOR . $this->module->config['environment'] . DIRECTORY_SEPARATOR . 'session.php',
            $configDir . DIRECTORY_SEPARATOR . 'session.php',

        );

        foreach ($configFiles as $configFile) {
            if (file_exists($configFile)) {
                $sessionConfig = require $configFile;

                if (is_array($sessionConfig) && isset($sessionConfig['driver'])) {
                    $app['config']['session.driver'] = $sessionConfig['driver'];
                    break;
                }
            }
        }
    }

    /**
     * Use a stacked client to include middlewares.
     *
     * @see Illuminate\Foundation\Application::getStackedClient()
     * @return \Stack\StackedHttpKernel
     */
    private function getStackedClient()
    {
        $method = new \ReflectionMethod(get_class($this->app), 'getStackedClient');
        $method->setAccessible(true);

        return $method->invoke($this->app);
    }
}
