<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfig;
use Codeception\Lib\Connector\Lumen as LumenConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\TestInterface;
use Codeception\Step;
use Codeception\Configuration;
use Codeception\Lib\ModuleContainer;
use Codeception\Subscriber\ErrorHandler;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
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
 * * cleanup: `boolean`, default `true` - all db queries will be run in transaction, which will be rolled back at the end of test.
 * * bootstrap: `string`, default `bootstrap/app.php` - Relative path to app.php config file.
 * * root: `string`, default `` - Root path of our application.
 * * packages: `string`, default `workbench` - Root path of application packages (if any).
 *
 * ## API
 *
 * * app - `Illuminate\Foundation\Application` instance
 * * client - `BrowserKit` client
 *
 */
class Lumen extends Framework implements ActiveRecord
{

    /**
     * @var \Laravel\Lumen\Application
     */
    public $app;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var bool
     */
    protected $booted = false;

    /**
     * Constructor.
     *
     * @param ModuleContainer $container
     * @param $config
     */
    public function __construct(ModuleContainer $container, $config = null)
    {
        $this->config = array_merge(
            array(
                'cleanup' => true,
                'bootstrap' => 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php',
                'root' => '',
                'packages' => 'workbench',
            ),
            (array) $config
        );

        parent::__construct($container);
    }

    /**
     * Initialize hook.
     */
    public function _initialize()
    {
        $this->initializeLumen();
    }

    /**
     * Before hook.
     *
     * @param \Codeception\TestInterface $test
     * @throws ModuleConfig
     */
    public function _before(TestInterface $test)
    {
        $this->initializeLumen();

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
     * Initialize the Lumen framework.
     *
     * @throws ModuleConfig
     */
    protected function initializeLumen()
    {
        if ($this->booted) {
            Facade::clearResolvedInstances();
        }

        $this->app = $this->bootApplication();
        $this->app->instance('request', new Request());
        $this->client = new LumenConnector($this->app);
        $this->client->followRedirects(true);

        $this->revertErrorHandler();
    }

    /**
     * Boot the Lumen application object.
     *
     * @return \Laravel\Lumen\Application
     * @throws \Codeception\Exception\ModuleConfig
     */
    protected function bootApplication()
    {
        $projectDir = explode($this->config['packages'], Configuration::projectDir())[0];
        $projectDir .= $this->config['root'];
        require $projectDir . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        $bootstrapFile = $projectDir . $this->config['bootstrap'];

        if (! file_exists($bootstrapFile)) {
            throw new ModuleConfig(
                $this,
                "Laravel bootstrap file not found in $bootstrapFile.\n"
                . "Please provide a valid path to it using 'bootstrap' config param. "
            );
        }

        $app = require $bootstrapFile;
        $this->booted = true;

        return $app;
    }

    /**
     * Revert back to the Codeception error handler,
     * because Laravel registers it's own error handler.
     */
    protected function revertErrorHandler()
    {
        $handler = new ErrorHandler();
        set_error_handler(array($handler, 'errorHandler'));
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

        if (! $route) {
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
        foreach ($this->app->getRoutes() as $route) {
            if ($route['method'] != 'GET') {
                return;
            }

            if (isset($route['action']['as']) && $route['action']['as'] == $routeName) {
                return $route;
            }
        }

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
     * Assert that the session has a given list of values.
     *
     * @param  string|array $key
     * @param  mixed $value
     * @return void
     */
    public function seeInSession($key, $value = null)
    {
        if (is_array($key)) {
            $this->seeSessionHasValues($key);
            return;
        }

        if (is_null($value)) {
            $this->assertTrue($this->app['session']->has($key));
        } else {
            $this->assertEquals($value, $this->app['session']->get($key));
        }
    }

    /**
     * Assert that the session has a given list of values.
     *
     * @param  array $bindings
     * @return void
     */
    public function seeSessionHasValues(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->seeInSession($value);
            } else {
                $this->seeInSession($key, $value);
            }
        }
    }

    /**
     * Set the currently logged in user for the application.
     * Takes either an object that implements the User interface or
     * an array of credentials.
     *
     * @param  \Illuminate\Contracts\Auth\User|array $user
     * @param  string|null $driver The authentication driver for Lumen <= 5.1.*, guard name for Lumen >= 5.2
     * @return void
     */
    public function amLoggedAs($user, $driver = null)
    {
        $guard = $auth = $this->app['auth'];
        if (method_exists($auth, 'driver')) {
            $guard = $auth->driver($driver);
        }
        if (method_exists($auth, 'guard')) {
            $guard = $auth->guard($driver);
        }
        if ($user instanceof Authenticatable) {
            $guard->setUser($user);
            return;
        }
        if (! $guard->attempt($user)) {
            $this->fail("Failed to login with credentials " . json_encode($user));
        }
    }

    /**
     * Logs user out
     */
    public function logout()
    {
        $this->app['auth']->logout();
    }

    /**
     * Checks that user is authenticated
     */
    public function seeAuthentication()
    {
        $this->assertTrue($this->app['auth']->check(), 'User is not logged in');
    }

    /**
     * Check that user is not authenticated
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

            if (! $model instanceof EloquentModel) {
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
            if (! $this->findModel($table, $attributes)) {
                $this->fail("Could not find $table with " . json_encode($attributes));
            }
        } else if (! $this->findRecord($table, $attributes)) {
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
        } else if ($this->findRecord($table, $attributes)) {
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
            if (! $model = $this->findModel($table, $attributes)) {
                $this->fail("Could not find $table with " . json_encode($attributes));
            }

            return $model;
        }

        if (! $record = $this->findRecord($table, $attributes)) {
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

        return (array) $query->first();
    }

}
