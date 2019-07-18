<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Connector\Lumen as LumenConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Lib\Shared\LaravelCommon;
use Codeception\Lib\ModuleContainer;
use Codeception\TestInterface;
use Codeception\Util\ReflectionHelper;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 *
 * This module allows you to run functional tests for Lumen.
 * Please try it and leave your feedback.
 *
 * ## Demo project
 * <https://github.com/janhenkgerritsen/codeception-lumen-sample>
 *
 * ## Status
 *
 * * Maintainer: **Jan-Henk Gerritsen**
 * * Stability: **dev**
 * * Contact: janhenkgerritsen@gmail.com
 *
 * ## Config
 *
 * * cleanup: `boolean`, default `true` - all database queries will be run in a transaction,
 *   which will be rolled back at the end of each test.
 * * bootstrap: `string`, default `bootstrap/app.php` - relative path to app.php config file.
 * * root: `string`, default `` - root path of the application.
 * * packages: `string`, default `workbench` - root path of application packages (if any).
 * * url: `string`, default `http://localhost` - the application URL
 *
 * ## API
 *
 * * app - `\Laravel\Lumen\Application`
 * * config - `array`
 *
 * ## Parts
 *
 * * ORM - only include the database methods of this module:
 *     * have
 *     * haveMultiple
 *     * haveRecord
 *     * grabRecord
 *     * seeRecord
 *     * dontSeeRecord
 */
class Lumen extends Framework implements ActiveRecord, PartedModule
{
    use LaravelCommon;

    /**
     * @var \Laravel\Lumen\Application
     */
    public $app;

    /**
     * @var array
     */
    public $config = [];

    /**
     * Constructor.
     *
     * @param ModuleContainer $container
     * @param array|null $config
     */
    public function __construct(ModuleContainer $container, $config = null)
    {
        $this->config = array_merge(
            [
                'cleanup' => true,
                'bootstrap' => 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php',
                'root' => '',
                'packages' => 'workbench',
                'url' => 'http://localhost',
            ],
            (array)$config
        );

        $projectDir = explode($this->config['packages'], Configuration::projectDir())[0];
        $projectDir .= $this->config['root'];

        $this->config['project_dir'] = $projectDir;
        $this->config['bootstrap_file'] = $projectDir . $this->config['bootstrap'];

        parent::__construct($container);
    }

    /**
     * @return array
     */
    public function _parts()
    {
        return ['orm'];
    }

    /**
     * Initialize hook.
     */
    public function _initialize()
    {
        $this->checkBootstrapFileExists();
        $this->registerAutoloaders();
    }

    /**
     * Before hook.
     *
     * @param \Codeception\TestInterface $test
     * @throws ModuleConfigException
     */
    public function _before(TestInterface $test)
    {
        $this->client = new LumenConnector($this);

        if ($this->app['db'] && $this->config['cleanup']) {
            $this->app['db']->beginTransaction();
        }
    }

    /**
     * After hook.
     *
     * @param \Codeception\TestInterface $test
     */
    public function _after(TestInterface $test)
    {
        if ($this->app['db'] && $this->config['cleanup']) {
            $this->app['db']->rollback();
        }

        // disconnect from DB to prevent "Too many connections" issue
        if ($this->app['db']) {
            $this->app['db']->disconnect();
        }
    }

    /**
     * Make sure the Lumen bootstrap file exists.
     *
     * @throws ModuleConfigException
     */
    protected function checkBootstrapFileExists()
    {
        $bootstrapFile = $this->config['bootstrap_file'];

        if (!file_exists($bootstrapFile)) {
            throw new ModuleConfigException(
                $this,
                "Lumen bootstrap file not found in $bootstrapFile.\n"
                . "Please provide a valid path using the 'bootstrap' config param. "
            );
        }
    }

    /**
     * Register autoloaders.
     */
    protected function registerAutoloaders()
    {
        require $this->config['project_dir'] . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    /**
     * Provides access the Lumen application object.
     *
     * @return \Laravel\Lumen\Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * @param \Laravel\Lumen\Application $app
     */
    public function setApplication($app)
    {
        $this->app = $app;
    }

    /**
     * Opens web page using route name and parameters.
     *
     * ```php
     * <?php
     * $I->amOnRoute('homepage');
     * ?>
     * ```
     *
     * @param $routeName
     * @param array $params
     */
    public function amOnRoute($routeName, $params = [])
    {
        $route = $this->getRouteByName($routeName);

        if (!$route) {
            $this->fail("Could not find route with name '$routeName'");
        }

        $url = $this->generateUrlForRoute($route, $params);
        $this->amOnPage($url);
    }

    /**
     * Get the route for a route name.
     *
     * @param string $routeName
     * @return array|null
     */
    private function getRouteByName($routeName)
    {
        if (isset($this->app->router) && $this->app->router instanceof \Laravel\Lumen\Routing\Router) {
            $router = $this->app->router;
        } else {
            // backward compatibility with lumen 5.3
            $router = $this->app;
        }
        foreach ($router->getRoutes() as $route) {
            if (isset($route['action']['as']) && $route['action']['as'] == $routeName) {
                return $route;
            }
        }
        $this->fail("Route with name '$routeName' does not exist");
        return null;
    }

    /**
     * Generate the URL for a route specification.
     * Replaces the route parameters from left to right with the parameters
     * passed in the $params array.
     *
     * @param array $route
     * @param array $params
     * @return string
     */
    private function generateUrlForRoute($route, $params)
    {
        $url = $route['uri'];

        while (count($params) > 0) {
            $param = array_shift($params);
            $url = preg_replace('/{.+?}/', $param, $url, 1);
        }

        return $url;
    }

    /**
     * Set the authenticated user for the next request.
     * This will not persist between multiple requests.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable
     * @param  string|null $driver The authentication driver for Lumen <= 5.1.*, guard name for Lumen >= 5.2
     * @return void
     */
    public function amLoggedAs($user, $driver = null)
    {
        if (!$user instanceof Authenticatable) {
            $this->fail(
                'The user passed to amLoggedAs() should be an instance of \\Illuminate\\Contracts\\Auth\\Authenticatable'
            );
        }

        $guard = $auth = $this->app['auth'];

        if (method_exists($auth, 'driver')) {
            $guard = $auth->driver($driver);
        }

        if (method_exists($auth, 'guard')) {
            $guard = $auth->guard($driver);
        }

        $guard->setUser($user);
    }

    /**
     * Checks that user is authenticated.
     */
    public function seeAuthentication()
    {
        $this->assertTrue($this->app['auth']->check(), 'User is not logged in');
    }
    /**
     * Check that user is not authenticated.
     */
    public function dontSeeAuthentication()
    {
        $this->assertFalse($this->app['auth']->check(), 'User is logged in');
    }

    /**
     * Return an instance of a class from the IoC Container.
     *
     * Example
     * ``` php
     * <?php
     * // In Lumen
     * App::bind('foo', function($app)
     * {
     *     return new FooBar;
     * });
     *
     * // Then in test
     * $service = $I->grabService('foo');
     *
     * // Will return an instance of FooBar, also works for singletons.
     * ?>
     * ```
     *
     * @param  string $class
     * @return mixed
     */
    public function grabService($class)
    {
        return $this->app[$class];
    }

    /**
     * Inserts record into the database.
     * If you pass the name of a database table as the first argument, this method returns an integer ID.
     * You can also pass the class name of an Eloquent model, in that case this method returns an Eloquent model.
     *
     * ``` php
     * <?php
     * $user_id = $I->haveRecord('users', array('name' => 'Davert')); // returns integer
     * $user = $I->haveRecord('App\User', array('name' => 'Davert')); // returns Eloquent model
     * ?>
     * ```
     *
     * @param string $table
     * @param array $attributes
     * @return integer|EloquentModel
     * @part orm
     */
    public function haveRecord($table, $attributes = [])
    {
        if (class_exists($table)) {
            $model = new $table;

            if (!$model instanceof EloquentModel) {
                throw new \RuntimeException("Class $table is not an Eloquent model");
            }

            $model->fill($attributes)->save();

            return $model;
        }

        try {
            return $this->app['db']->table($table)->insertGetId($attributes);
        } catch (\Exception $e) {
            $this->fail("Could not insert record into table '$table':\n\n" . $e->getMessage());
        }
    }

    /**
     * Checks that record exists in database.
     * You can pass the name of a database table or the class name of an Eloquent model as the first argument.
     *
     * ``` php
     * <?php
     * $I->seeRecord('users', array('name' => 'davert'));
     * $I->seeRecord('App\User', array('name' => 'davert'));
     * ?>
     * ```
     *
     * @param string $table
     * @param array $attributes
     * @part orm
     */
    public function seeRecord($table, $attributes = [])
    {
        if (class_exists($table)) {
            if (!$this->findModel($table, $attributes)) {
                $this->fail("Could not find $table with " . json_encode($attributes));
            }
        } elseif (!$this->findRecord($table, $attributes)) {
            $this->fail("Could not find matching record in table '$table'");
        }
    }

    /**
     * Checks that record does not exist in database.
     * You can pass the name of a database table or the class name of an Eloquent model as the first argument.
     *
     * ``` php
     * <?php
     * $I->dontSeeRecord('users', array('name' => 'davert'));
     * $I->dontSeeRecord('App\User', array('name' => 'davert'));
     * ?>
     * ```
     *
     * @param string $table
     * @param array $attributes
     * @part orm
     */
    public function dontSeeRecord($table, $attributes = [])
    {
        if (class_exists($table)) {
            if ($this->findModel($table, $attributes)) {
                $this->fail("Unexpectedly found matching $table with " . json_encode($attributes));
            }
        } elseif ($this->findRecord($table, $attributes)) {
            $this->fail("Unexpectedly found matching record in table '$table'");
        }
    }

    /**
     * Retrieves record from database
     * If you pass the name of a database table as the first argument, this method returns an array.
     * You can also pass the class name of an Eloquent model, in that case this method returns an Eloquent model.
     *
     * ``` php
     * <?php
     * $record = $I->grabRecord('users', array('name' => 'davert')); // returns array
     * $record = $I->grabRecord('App\User', array('name' => 'davert')); // returns Eloquent model
     * ?>
     * ```
     *
     * @param string $table
     * @param array $attributes
     * @return array|EloquentModel
     * @part orm
     */
    public function grabRecord($table, $attributes = [])
    {
        if (class_exists($table)) {
            if (!$model = $this->findModel($table, $attributes)) {
                $this->fail("Could not find $table with " . json_encode($attributes));
            }

            return $model;
        }

        if (!$record = $this->findRecord($table, $attributes)) {
            $this->fail("Could not find matching record in table '$table'");
        }

        return $record;
    }

    /**
     * @param string $modelClass
     * @param array $attributes
     *
     * @return EloquentModel
     */
    protected function findModel($modelClass, $attributes = [])
    {
        $model = new $modelClass;

        if (!$model instanceof EloquentModel) {
            throw new \RuntimeException("Class $modelClass is not an Eloquent model");
        }

        $query = $model->newQuery();
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }

    /**
     * @param string $table
     * @param array $attributes
     * @return array
     */
    protected function findRecord($table, $attributes = [])
    {
        $query = $this->app['db']->table($table);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return (array)$query->first();
    }

    /**
     * Use Lumen's model factory to create a model.
     * Can only be used with Lumen 5.1 and later.
     *
     * ``` php
     * <?php
     * $I->have('App\User');
     * $I->have('App\User', ['name' => 'John Doe']);
     * $I->have('App\User', [], 'admin');
     * ?>
     * ```
     *
     * @see https://lumen.laravel.com/docs/master/testing#model-factories
     * @param string $model
     * @param array $attributes
     * @param string $name
     * @return mixed
     * @part orm
     */
    public function have($model, $attributes = [], $name = 'default')
    {
        try {
            return $this->modelFactory($model, $name)->create($attributes);
        } catch (\Exception $e) {
            $this->fail("Could not create model: \n\n" . get_class($e) . "\n\n" . $e->getMessage());
        }
    }

    /**
     * Use Laravel's model factory to create multiple models.
     * Can only be used with Lumen 5.1 and later.
     *
     * ``` php
     * <?php
     * $I->haveMultiple('App\User', 10);
     * $I->haveMultiple('App\User', 10, ['name' => 'John Doe']);
     * $I->haveMultiple('App\User', 10, [], 'admin');
     * ?>
     * ```
     *
     * @see https://lumen.laravel.com/docs/master/testing#model-factories
     * @param string $model
     * @param int $times
     * @param array $attributes
     * @param string $name
     * @return mixed
     * @part orm
     */
    public function haveMultiple($model, $times, $attributes = [], $name = 'default')
    {
        try {
            return $this->modelFactory($model, $name, $times)->create($attributes);
        } catch (\Exception $e) {
            $this->fail("Could not create model: \n\n" . get_class($e) . "\n\n" . $e->getMessage());
        }
    }
    

    /**
     * Use Lumen's model factory to make a model instance.
     * Can only be used with Lumen 5.1 and later.
     *
     * ``` php
     * <?php
     * $I->make('App\User');
     * $I->make('App\User', ['name' => 'John Doe']);
     * $I->make('App\User', [], 'admin');
     * ?>
     * ```
     *
     * @see https://lumen.laravel.com/docs/master/testing#model-factories
     * @param string $model
     * @param array $attributes
     * @param string $name
     * @return mixed
     * @part orm
     */
    public function make($model, $attributes = [], $name = 'default')
    {
        try {
            return $this->modelFactory($model, $name)->make($attributes);
        } catch (\Exception $e) {
            $this->fail("Could not make model: \n\n" . get_class($e) . "\n\n" . $e->getMessage());
        }
    }
    
    /**
     * Use Laravel's model factory to make multiple model instances.
     * Can only be used with Lumen 5.1 and later.
     *
     * ``` php
     * <?php
     * $I->makeMultiple('App\User', 10);
     * $I->makeMultiple('App\User', 10, ['name' => 'John Doe']);
     * $I->makeMultiple('App\User', 10, [], 'admin');
     * ?>
     * ```
     *
     * @see https://lumen.laravel.com/docs/master/testing#model-factories
     * @param string $model
     * @param int $times
     * @param array $attributes
     * @param string $name
     * @return mixed
     * @part orm
     */
    public function makeMultiple($model, $times, $attributes = [], $name = 'default')
    {
        try {
            return $this->modelFactory($model, $name, $times)->make($attributes);
        } catch (\Exception $e) {
            $this->fail("Could not make model: \n\n" . get_class($e) . "\n\n" . $e->getMessage());
        }
    }

    /**
     * @param string $model
     * @param string $name
     * @param int $times
     * @return \Illuminate\Database\Eloquent\FactoryBuilder
     * @throws ModuleException
     */
    protected function modelFactory($model, $name, $times = 1)
    {
        if (!function_exists('factory')) {
            throw new ModuleException($this, 'The factory() method does not exist. ' .
                'This functionality relies on Lumen model factories, which were introduced in Lumen 5.1.');
        }

        return factory($model, $name, $times);
    }

    /**
     * Returns a list of recognized domain names.
     * This elements of this list are regular expressions.
     *
     * @return array
     */
    protected function getInternalDomains()
    {
        $server = ReflectionHelper::readPrivateProperty($this->client, 'server');

        return ['/^' . str_replace('.', '\.', $server['HTTP_HOST']) . '$/'];
    }
}
