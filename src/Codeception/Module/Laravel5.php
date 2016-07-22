<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Connector\Laravel5 as LaravelConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Lib\ModuleContainer;
use Codeception\Subscriber\ErrorHandler;
use Codeception\Util\ReflectionHelper;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 *
 * This module allows you to run functional tests for Laravel 5.
 * It should **not** be used for acceptance tests.
 * See the Acceptance tests section below for more details.
 *
 * As of Codeception 2.2 this module only works for Laravel 5.1 and later releases.
 * If you want to test a Laravel 5.0 application you have to use Codeception 2.1.
 * You can also upgrade your Laravel application to 5.1, for more details check the Laravel Upgrade Guide at <https://laravel.com/docs/master/upgrade>.
 *
 * ## Demo project
 * <https://github.com/janhenkgerritsen/codeception-laravel5-sample>
 *
 * ## Status
 *
 * * Maintainer: **Jan-Henk Gerritsen**
 * * Stability: **stable**
 *
 * ## Example
 *
 *     modules:
 *         enabled:
 *             - Laravel5:
 *                 environment_file: .env.testing
 *
 * ## Config
 *
 * * cleanup: `boolean`, default `true` - all db queries will be run in transaction, which will be rolled back at the end of test.
 * * run_database_migrations: `boolean`, default `false` - enable to run database migrations before each test.
 * * environment_file: `string`, default `.env` - The .env file to load for the tests.
 * * bootstrap: `string`, default `bootstrap/app.php` - Relative path to app.php config file.
 * * root: `string`, default `` - Root path of our application.
 * * packages: `string`, default `workbench` - Root path of application packages (if any).
 * * disable_exception_handling: `boolean`, default `true` - disable Laravel exception handling
 * * disable_middleware: `boolean`, default `false` - disable all middleware.
 * * disable_events: `boolean`, default `false` - disable events (does not disable model events).
 * * disable_model_events: `boolean`, default `false` - disable model events.
 * * url: `string`, default `` - The application URL.
 *
 * ## API
 *
 * * app - `Illuminate\Foundation\Application` instance
 * * client - `\Symfony\Component\BrowserKit\Client` instance
 *
 * ## Parts
 *
 * * ORM - include only haveRecord/grabRecord/seeRecord/dontSeeRecord actions
 *
 * ## Acceptance tests
 *
 * You should not use this module for acceptance tests.
 * If you want to use Laravel functionality with your acceptance tests,
 * for example to do test setup, you can initialize the Laravel functionality
 * by adding the following lines of code to your suite `_bootstrap.php` file:
 *
 *     require 'bootstrap/autoload.php';
 *     $app = require 'bootstrap/app.php';
 *     $app->loadEnvironmentFrom('.env.testing');
 *     $app->instance('request', new \Illuminate\Http\Request);
 *     $app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();
 *
 *
 */
class Laravel5 extends Framework implements ActiveRecord, PartedModule
{

    /**
     * @var \Illuminate\Foundation\Application
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
                'run_database_migrations' => false,
                'environment_file' => '.env',
                'bootstrap' => 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php',
                'root' => '',
                'packages' => 'workbench',
                'disable_exception_handling' => true,
                'disable_middleware' => false,
                'disable_events' => false,
                'disable_model_events' => false,
            ],
            (array)$config
        );

        $projectDir = explode($this->config['packages'], \Codeception\Configuration::projectDir())[0];
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
        $this->revertErrorHandler();
    }

    /**
     * Before hook.
     *
     * @param \Codeception\TestInterface $test
     */
    public function _before(\Codeception\TestInterface $test)
    {
        $this->client = new LaravelConnector($this);

        if ($this->config['run_database_migrations']) {
            // Must be called before database transactions are started
            $this->callArtisan('migrate');
        }

        if (isset($this->app['db']) && $this->config['cleanup']) {
            $this->app['db']->beginTransaction();
        }
    }

    /**
     * After hook.
     *
     * @param \Codeception\TestInterface $test
     */
    public function _after(\Codeception\TestInterface $test)
    {
        if (isset($this->app['db']) && $this->config['cleanup']) {
            $this->app['db']->rollback();
        }

        if (isset($this->app['auth'])) {
            $this->app['auth']->logout();
        }

        if (isset($this->app['session'])) {
            $this->app['session']->flush();
        }

        if (isset($this->app['cache'])) {
            $this->app['cache']->flush();
        }

        // disconnect from DB to prevent "Too many connections" issue
        if (isset($this->app['db'])) {
            $this->app['db']->disconnect();
        }
    }

    /**
     * Make sure the Laravel bootstrap file exists.
     *
     * @throws ModuleConfig
     */
    protected function checkBootstrapFileExists()
    {
        $bootstrapFile = $this->config['bootstrap_file'];

        if (!file_exists($bootstrapFile)) {
            throw new ModuleConfigException(
                $this,
                "Laravel bootstrap file not found in $bootstrapFile.\n"
                . "Please provide a valid path to it using 'bootstrap' config param. "
            );
        }
    }

    /**
     * Register Laravel autoloaders.
     */
    protected function registerAutoloaders()
    {
        require $this->config['project_dir'] . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        \Illuminate\Support\ClassLoader::register();
    }

    /**
     * Revert back to the Codeception error handler,
     * becauses Laravel registers it's own error handler.
     */
    protected function revertErrorHandler()
    {
        $handler = new ErrorHandler();
        set_error_handler(array($handler, 'errorHandler'));
    }

    /**
     * Provides access the Laravel application object.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * @param $app
     */
    public function setApplication($app)
    {
        $this->app = $app;
    }

    /**
     * Enable Laravel exception handling.
     *
     * ``` php
     * <?php
     * $I->enableExceptionHandling();
     * ?>
     * ```
     */
    public function enableExceptionHandling()
    {
        $this->client->enableExceptionHandling();
    }

    /**
     * Disable Laravel exception handling.
     *
     * ``` php
     * <?php
     * $I->disableExceptionHandling();
     * ?>
     * ```
     */
    public function disableExceptionHandling()
    {
        $this->client->disableExceptionHandling();
    }

    /**
     * Disable middleware for the next requests.
     *
     * ``` php
     * <?php
     * $I->disableMiddleware();
     * ?>
     * ```
     */
    public function disableMiddleware()
    {
        $this->client->disableMiddleware();
    }

    /**
     * Disable events for the next requests.
     * This method does not disable model events.
     * To disable model events you have to use the disableModelEvents() method.
     *
     * ``` php
     * <?php
     * $I->disableEvents();
     * ?>
     * ```
     */
    public function disableEvents()
    {
        $this->client->disableEvents();
    }

    /**
     * Disable model events for the next requests.
     *
     * ``` php
     * <?php
     * $I->disableModelEvents();
     * ?>
     * ```
     */
    public function disableModelEvents()
    {
        $this->client->disableModelEvents();
    }

    /**
     * Make sure events fired during the test.
     *
     * ``` php
     * <?php
     * $I->seeEventTriggered('App\MyEvent');
     * $I->seeEventTriggered(new App\Events\MyEvent());
     * $I->seeEventTriggered('App\MyEvent', 'App\MyOtherEvent');
     * $I->seeEventTriggered(['App\MyEvent', 'App\MyOtherEvent']);
     * ?>
     * ```
     * @param $events
     */
    public function seeEventTriggered($events)
    {
        $events = is_array($events) ? $events : func_get_args();

        foreach ($events as $event) {
            if (!$this->client->eventTriggered($event)) {
                if (is_object($event)) {
                    $event = get_class($event);
                }

                $this->fail("The '$event' event did not trigger");
            }
        }
    }

    /**
     * Make sure events did not fire during the test.
     *
     * ``` php
     * <?php
     * $I->dontSeeEventTriggered('App\MyEvent');
     * $I->dontSeeEventTriggered(new App\Events\MyEvent());
     * $I->dontSeeEventTriggered('App\MyEvent', 'App\MyOtherEvent');
     * $I->dontSeeEventTriggered(['App\MyEvent', 'App\MyOtherEvent']);
     * ?>
     * ```
     * @param $events
     */
    public function dontSeeEventTriggered($events)
    {
        $events = is_array($events) ? $events : func_get_args();

        foreach ($events as $event) {
            if ($this->client->eventTriggered($event)) {
                if (is_object($event)) {
                    $event = get_class($event);
                }

                $this->fail("The '$event' event triggered");
            }
        }
    }

    /**
     * Call an Artisan command.
     *
     * ``` php
     * <?php
     * $I->callArtisan('command:name');
     * $I->callArtisan('command:name', ['parameter' => 'value']);
     * ?>
     * ```

     * @param string $command
     * @param array $parameters
     */
    public function callArtisan($command, $parameters = [])
    {
        $console = $this->app->make('Illuminate\Contracts\Console\Kernel');
        $console->call($command, $parameters);

        return trim($console->output());
    }

    /**
     * Opens web page using route name and parameters.
     *
     * ``` php
     * <?php
     * $I->amOnRoute('posts.create');
     * ?>
     * ```
     *
     * @param $routeName
     * @param array $params
     */
    public function amOnRoute($routeName, $params = [])
    {
        $route = $this->getRouteByName($routeName);

        $absolute = !is_null($route->domain());
        $url = $this->app['url']->route($routeName, $params, $absolute);
        $this->amOnPage($url);
    }

    /**
     * Checks that current url matches route
     *
     * ``` php
     * <?php
     * $I->seeCurrentRouteIs('posts.index');
     * ?>
     * ```
     * @param $routeName
     */
    public function seeCurrentRouteIs($routeName)
    {
        $this->getRouteByName($routeName); // Fails if route does not exists

        $currentRoute = $this->app->request->route();
        $currentRouteName = $currentRoute ? $currentRoute->getName() : '';

        if ($currentRouteName != $routeName) {
            $message = empty($currentRouteName)
                ? "Current route has no name"
                : "Current route is \"$currentRouteName\"";
            $this->fail($message);
        }
    }

    /**
     * Opens web page by action name
     *
     * ``` php
     * <?php
     * $I->amOnAction('PostsController@index');
     * ?>
     * ```
     *
     * @param $action
     * @param array $params
     */
    public function amOnAction($action, $params = [])
    {
        $route = $this->getRouteByAction($action);
        $absolute = !is_null($route->domain());
        $url = $this->app['url']->action($action, $params, $absolute);

        $this->amOnPage($url);
    }

    /**
     * Checks that current url matches action
     *
     * ``` php
     * <?php
     * $I->seeCurrentActionIs('PostsController@index');
     * ?>
     * ```
     *
     * @param $action
     */
    public function seeCurrentActionIs($action)
    {
        $this->getRouteByAction($action); // Fails if route does not exists
        $currentRoute = $this->app->request->route();
        $currentAction = $currentRoute ? $currentRoute->getActionName() : '';
        $currentAction = ltrim(str_replace($this->getRootControllerNamespace(), "", $currentAction), '\\');

        if ($currentAction != $action) {
            $this->fail("Current action is \"$currentAction\"");
        }
    }

    /**
     * @param $routeName
     * @return mixed
     */
    protected function getRouteByName($routeName)
    {
        if (!$route = $this->app['routes']->getByName($routeName)) {
            $this->fail("Route with name '$routeName' does not exist");
        }

        return $route;
    }

    /**
     * @param string $action
     * @return \Illuminate\Routing\Route
     */
    protected function getRouteByAction($action)
    {
        $namespacedAction = $this->actionWithNamespace($action);

        if (!$route = $this->app['routes']->getByAction($namespacedAction)) {
            $this->fail("Action '$action' does not exist");
        }

        return $route;
    }

    /**
     * Normalize an action to full namespaced action.
     *
     * @param string $action
     * @return string
     */
    protected function actionWithNamespace($action)
    {
        $rootNamespace = $this->getRootControllerNamespace();

        if ($rootNamespace && !(strpos($action, '\\') === 0)) {
            return $rootNamespace . '\\' . $action;
        } else {
            return trim($action, '\\');
        }
    }

    /**
     * Get the root controller namespace for the application.
     *
     * @return string
     */
    protected function getRootControllerNamespace()
    {
        $urlGenerator = $this->app['url'];
        $reflection = new \ReflectionClass($urlGenerator);

        $property = $reflection->getProperty('rootNamespace');
        $property->setAccessible(true);

        return $property->getValue($urlGenerator);
    }

    /**
     * Assert that a session variable exists.
     *
     * ``` php
     * <?php
     * $I->seeInSession('key');
     * $I->seeInSession('key', 'value');
     * ?>
     * ```
     *
     * @param  string|array $key
     * @param  mixed|null $value
     * @return void
     */
    public function seeInSession($key, $value = null)
    {
        if (is_array($key)) {
            $this->seeSessionHasValues($key);
            return;
        }

        if (! $this->app['session']->has($key)) {
            $this->fail("No session variable with key '$key'");
        }

        if (! is_null($value)) {
            $this->assertEquals($value, $this->app['session']->get($key));
        }
    }

    /**
     * Assert that the session has a given list of values.
     *
     * ``` php
     * <?php
     * $I->seeSessionHasValues(['key1', 'key2']);
     * $I->seeSessionHasValues(['key1' => 'value1', 'key2' => 'value2']);
     * ?>
     * ```
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
     * Assert that form errors are bound to the View.
     *
     * ``` php
     * <?php
     * $I->seeFormHasErrors();
     * ?>
     * ```
     *
     * @return bool
     */
    public function seeFormHasErrors()
    {
        $viewErrorBag = $this->app->make('view')->shared('errors');
        if (count($viewErrorBag) == 0) {
            $this->fail("There are no form errors");
        }
    }

    /**
     * Assert that there are no form errors bound to the View.
     *
     * ``` php
     * <?php
     * $I->dontSeeFormErrors();
     * ?>
     * ```
     *
     * @return bool
     */
    public function dontSeeFormErrors()
    {
        $viewErrorBag = $this->app->make('view')->shared('errors');
        if (count($viewErrorBag) > 0) {
            $this->fail("Found the following form errors: \n\n" . $viewErrorBag->toJson(JSON_PRETTY_PRINT));
        }
    }

    /**
     * Assert that specific form error messages are set in the view.
     *
     * This method calls `seeFormErrorMessage` for each entry in the `$bindings` array.
     *
     * ``` php
     * <?php
     * $I->seeFormErrorMessages([
     *     'username' => 'Invalid Username',
     *     'password' => null,
     * ]);
     * ?>
     * ```
     * @param array $bindings
     */
    public function seeFormErrorMessages(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            $this->seeFormErrorMessage($key, $value);
        }
    }

    /**
     * Assert that a specific form error message is set in the view.
     *
     * If you want to assert that there is a form error message for a specific key
     * but don't care about the actual error message you can omit `$expectedErrorMessage`.
     *
     * If you do pass `$expectedErrorMessage`, this method checks if the actual error message for a key
     * contains `$expectedErrorMessage`.
     *
     * ``` php
     * <?php
     * $I->seeFormErrorMessage('username');
     * $I->seeFormErrorMessage('username', 'Invalid Username');
     * ?>
     * ```
     * @param string $key
     * @param string|null $expectedErrorMessage
     */
    public function seeFormErrorMessage($key, $expectedErrorMessage = null)
    {
        $viewErrorBag = $this->app['view']->shared('errors');

        if (!($viewErrorBag->has($key))) {
            $this->fail("No form error message for key '$key'\n");
        }

        if (! is_null($expectedErrorMessage)) {
            $this->assertContains($expectedErrorMessage, $viewErrorBag->first($key));
        }
    }

    /**
     * Set the currently logged in user for the application.
     * Takes either an object that implements the User interface or
     * an array of credentials.
     *
     * ``` php
     * <?php
     * // provide array of credentials
     * $I->amLoggedAs(['username' => 'jane@example.com', 'password' => 'password']);
     *
     * // provide User object
     * $I->amLoggedAs( new User );
     *
     * // can be verified with $I->seeAuthentication();
     * ?>
     * ```
     * @param  \Illuminate\Contracts\Auth\User|array $user
     * @param  string|null $driver The authentication driver for Laravel <= 5.1.*, guard name for Laravel >= 5.2
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
            $guard->login($user);
            return;
        }

        if (! $guard->attempt($user)) {
            $this->fail("Failed to login with credentials " . json_encode($user));
        }
    }

    /**
     * Logout user.
     */
    public function logout()
    {
        $this->app['auth']->logout();
    }

    /**
     * Checks that a user is authenticated.
     * You can specify the guard that should be use for Laravel >= 5.2.
     * @param string|null $guard
     */
    public function seeAuthentication($guard = null)
    {
        $auth = $this->app['auth'];

        if (method_exists($auth, 'guard')) {
            $auth = $auth->guard($guard);
        }

        if (! $auth->check()) {
            $this->fail("There is no authenticated user");
        }
    }

    /**
     * Check that user is not authenticated.
     * You can specify the guard that should be use for Laravel >= 5.2.
     * @param string|null $guard
     */
    public function dontSeeAuthentication($guard = null)
    {
        $auth = $this->app['auth'];

        if (method_exists($auth, 'guard')) {
            $auth = $auth->guard($guard);
        }

        if ($auth->check()) {
            $this->fail("There is an authenticated user");
        }
    }

    /**
     * Return an instance of a class from the Laravel service container.
     * (https://laravel.com/docs/master/container)
     *
     * ``` php
     * <?php
     * // In Laravel
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
     * Add a binding to the Laravel service container.
     * (https://laravel.com/docs/master/container)
     *
     * ``` php
     * <?php
     * $I->haveBinding('My\Interface', 'My\Implementation');
     * ?>
     * ```
     *
     * @param $abstract
     * @param $concrete
     */
    public function haveBinding($abstract, $concrete)
    {
        $this->client->haveBinding($abstract, $concrete);
    }

    /**
     * Add a singleton binding to the Laravel service container.
     * (https://laravel.com/docs/master/container)
     *
     * ``` php
     * <?php
     * $I->haveSingleton('My\Interface', 'My\Singleton');
     * ?>
     * ```
     *
     * @param $abstract
     * @param $concrete
     */
    public function haveSingleton($abstract, $concrete)
    {
        $this->client->haveBinding($abstract, $concrete, true);
    }

    /**
     * Add a contextual binding to the Laravel service container.
     * (https://laravel.com/docs/master/container)
     *
     * ``` php
     * <?php
     * $I->haveContextualBinding('My\Class', '$variable', 'value');
     *
     * // This is similar to the following in your Laravel application
     * $app->when('My\Class')
     *     ->needs('$variable')
     *     ->give('value');
     * ?>
     * ```
     *
     * @param $concrete
     * @param $abstract
     * @param $implementation
     */
    public function haveContextualBinding($concrete, $abstract, $implementation)
    {
        $this->client->haveContextualBinding($concrete, $abstract, $implementation);
    }

    /**
     * Add an instance binding to the Laravel service container.
     * (https://laravel.com/docs/master/container)
     *
     * ``` php
     * <?php
     * $I->haveInstance('My\Class', new My\Class());
     * ?>
     * ```
     *
     * @param $abstract
     * @param $instance
     */
    public function haveInstance($abstract, $instance)
    {
        $this->client->haveInstance($abstract, $instance);
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

    /*
     * Use Laravel's model factory to create a model.
     * Can only be used with Laravel 5.1 and later.
     *
     * ``` php
     * <?php
     * $I->have('App\User');
     * $I->have('App\User', ['name' => 'John Doe']);
     * $I->have('App\User', [], 'admin');
     * ?>
     * ```
     *
     * @see http://laravel.com/docs/5.1/testing#model-factories
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
        } catch(\Exception $e) {
            $this->fail("Could not create model: \n\n" . get_class($e) . "\n\n" . $e->getMessage());
        }
    }

    /*
     * Use Laravel's model factory to create multiple models.
     * Can only be used with Laravel 5.1 and later.
     *
     * ``` php
     * <?php
     * $I->haveMultiple('App\User', 10);
     * $I->haveMultiple('App\User', 10, ['name' => 'John Doe']);
     * $I->haveMultiple('App\User', 10, [], 'admin');
     * ?>
     * ```
     *
     * @see http://laravel.com/docs/5.1/testing#model-factories
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
        } catch(\Exception $e) {
            $this->fail("Could not create model: \n\n" . get_class($e) . "\n\n" . $e->getMessage());
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
        if (! function_exists('factory')) {
            throw new ModuleException($this, 'The factory() method does not exist. ' .
                'This functionality relies on Laravel model factories, which were introduced in Laravel 5.1.');
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
        $internalDomains = [$this->getApplicationDomainRegex()];

        foreach ($this->app['routes'] as $route) {
            if (!is_null($route->domain())) {
                $internalDomains[] = $this->getDomainRegex($route);
            }
        }

        return array_unique($internalDomains);
    }

    /**
     * @return string
     */
    private function getApplicationDomainRegex()
    {
        $server = ReflectionHelper::readPrivateProperty($this->client, 'server');
        $domain = $server['HTTP_HOST'];

        return '/^' . str_replace('.', '\.', $domain) . '$/';
    }

    /**
     * Get the regex for matching the domain part of this route.
     *
     * @param \Illuminate\Routing\Route $route
     * @return string
     */
    private function getDomainRegex($route)
    {
        ReflectionHelper::invokePrivateMethod($route, 'compileRoute');
        $compiledRoute = ReflectionHelper::readPrivateProperty($route, 'compiled');

        return $compiledRoute->getHostRegex();
    }
}
