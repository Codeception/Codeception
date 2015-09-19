<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Connector\Laravel5 as LaravelConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Lib\Interfaces\SupportsDomainRouting;
use Codeception\Lib\ModuleContainer;
use Codeception\Subscriber\ErrorHandler;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Facade;

/**
 *
 * This module allows you to run functional tests for Laravel 5.
 * Please try it and leave your feedback.
 * The module is based on the Laravel 4 module by Davert.
 *
 * ## Demo project
 * <https://github.com/janhenkgerritsen/codeception-laravel5-sample>
 *
 * ## Status
 *
 * * Maintainer: **Jan-Henk Gerritsen**
 * * Stability: **dev**
 * * Contact: janhenkgerritsen@gmail.com
 *
 * ## Example
 *
 *     modules:
 *         enabled:
 *             - Laravel5
 *
 * ## Config
 *
 * * cleanup: `boolean`, default `true` - all db queries will be run in transaction, which will be rolled back at the end of test.
 * * environment_file: `string`, default `.env` - The .env file to load for the tests.
 * * bootstrap: `string`, default `bootstrap/app.php` - Relative path to app.php config file.
 * * root: `string`, default `` - Root path of our application.
 * * packages: `string`, default `workbench` - Root path of application packages (if any).
 * * disable_middleware: `boolean`, default `false` - disable all middleware.
 * * disable_events: `boolean`, default `false` - disable all events.
 *
 * ## API
 *
 * * app - `Illuminate\Foundation\Application` instance
 * * client - `BrowserKit` client
 *
 * ## Parts
 *
 * * ORM - include only haveRecord/grabRecord/seeRecord/dontSeeRecord actions
 *
 */
class Laravel5 extends Framework implements ActiveRecord, PartedModule, SupportsDomainRouting
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
                'environment_file' => '.env',
                'bootstrap' => 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php',
                'root' => '',
                'packages' => 'workbench',
                'disable_middleware' => false,
                'disable_events' => false,
            ],
            (array)$config
        );

        $projectDir = explode($this->config['packages'], \Codeception\Configuration::projectDir())[0];
        $projectDir .= $this->config['root'];

        $this->config['project_dir'] = $projectDir;
        $this->config['bootstrap_file'] = $projectDir . $this->config['bootstrap'];

        parent::__construct($container);
    }

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
        $this->client = new LaravelConnector($this);
    }

    /**
     * Before hook.
     *
     * @param \Codeception\TestCase $test
     */
    public function _before(\Codeception\TestCase $test)
    {
        if ($this->app['db'] && $this->config['cleanup']) {
            $this->app['db']->beginTransaction();
        }

        if ($this->app['auth']) {
            $this->app['auth']->logout();
        }

        if ($this->app['session']) {
            // Destroy existing sessions of previous tests
            $this->app['session']->migrate(true);
        }

        $this->client->clearExpectedEvents();
    }

    /**
     * After hook.
     *
     * @param \Codeception\TestCase $test
     */
    public function _after(\Codeception\TestCase $test)
    {
        if ($this->app['db'] && $this->config['cleanup']) {
            $this->app['db']->rollback();
        }

        if ($missedEvents = $this->client->missedEvents()) {
            $test->fail('The following events did not fire: ' . implode(',', $missedEvents));
        }
    }

    /**
     * After step hook.
     *
     * @param \Codeception\Step $step
     */
    public function _afterStep(\Codeception\Step $step)
    {
        parent::_afterStep($step);

        Facade::clearResolvedInstances();
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
                "Laravel bootstrap file not found in $bootstrapFile.\nPlease provide a valid path to it using 'bootstrap' config param. "
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
        $this->config['disable_middleware'] = true;
    }

    /**
     * Enable middleware for the next requests.
     *
     * ``` php
     * <?php
     * $I->enableMiddleware();
     * ?>
     * ```
     */
    public function enableMiddleware()
    {
        $this->config['disable_middleware'] = false;
    }

    /**
     * Disable events for the next requests.
     *
     * ``` php
     * <?php
     * $I->disableEvents();
     * ?>
     * ```
     */
    public function disableEvents()
    {
        $this->config['disable_events'] = true;
    }

    /**
     * Enable events for the next requests.
     *
     * ``` php
     * <?php
     * $I->enableEvents();
     * ?>
     * ```
     */
    public function enableEvents()
    {
        $this->config['disable_events'] = false;
    }

    /**
     * Make sure events fired during the test.
     *
     * ``` php
     * <?php
     * $I->expectEvents('App\MyEvent');
     * $I->expectEvents('App\MyEvent', 'App\MyOtherEvent');
     * $I->expectEvents(['App\MyEvent', 'App\MyOtherEvent']);
     * ?>
     * ```
     * @param $events
     */
    public function expectEvents($events)
    {
        $events = is_array($events) ? $events : func_get_args();

        foreach ($events as $expectedEvent) {
            $this->client->addExpectedEvent($expectedEvent);
        }
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
        $route = $this->app['routes']->getByName($routeName);

        if (!$route) {
            $this->fail("Route with name '$routeName' does not exist");
        }

        $absolute = !is_null($route->domain());
        $url = $this->app['url']->route($routeName, $params, $absolute);
        $this->amOnPage($url);
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
        $namespacedAction = $this->actionWithNamespace($action);
        $route = $this->app['routes']->getByAction($namespacedAction);

        if (!$route) {
            $this->fail("Action '$action' does not exist");
        }

        $absolute = !is_null($route->domain());
        $url = $this->app['url']->action($action, $params, $absolute);
        $this->amOnPage($url);
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
     * Checks that current url matches route
     *
     * ``` php
     * <?php
     * $I->seeCurrentRouteIs('posts.index');
     * ?>
     * ```
     * @param $route
     * @param array $params
     */
    public function seeCurrentRouteIs($route, $params = array())
    {
        $this->seeCurrentUrlEquals($this->app['url']->route($route, $params, false));
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
     * @param array $params
     */
    public function seeCurrentActionIs($action, $params = array())
    {
        $this->seeCurrentUrlEquals($this->app['url']->action($action, $params, false));
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

        if (is_null($value)) {
            $this->assertTrue($this->app['session']->has($key));
        } else {
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
        $this->assertTrue(count($viewErrorBag) > 0);
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
        $this->assertTrue(count($viewErrorBag) == 0);
    }

    /**
     * Assert that specific form error messages are set in the view.
     *
     * Useful for validation messages e.g.
     *  return `Redirect::to('register')->withErrors($validator);`
     *
     * Example of Usage
     *
     * ``` php
     * <?php
     * $I->seeFormErrorMessages(array('username'=>'Invalid Username'));
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
     * Assert that specific form error message is set in the view.
     *
     * Useful for validation messages and generally messages array
     *  e.g.
     *  return `Redirect::to('register')->withErrors($validator);`
     *
     * Example of Usage
     *
     * ``` php
     * <?php
     * $I->seeFormErrorMessage('username', 'Invalid Username');
     * ?>
     * ```
     * @param string $key
     * @param string $errorMessage
     */
    public function seeFormErrorMessage($key, $errorMessage)
    {
        $viewErrorBag = $this->app['view']->shared('errors');

        $this->assertEquals($errorMessage, $viewErrorBag->first($key));
    }

    /**
     * Set the currently logged in user for the application.
     * Takes either an object that implements the User interface or
     * an array of credentials.
     *
     * Example of Usage
     *
     * ``` php
     * <?php
     * // provide array of credentials
     * $I->amLoggedAs(['username' => 'jane@example.com', 'password' => 'password']);
     *
     * // provide User object
     * $I->amLoggesAs( new User );
     *
     * // can be verified with $I->seeAuthentication();
     * ?>
     * ```
     * @param  \Illuminate\Contracts\Auth\User|array $user
     * @param  string|null $driver 'eloquent', 'database', or custom driver
     * @return void
     */
    public function amLoggedAs($user, $driver = null)
    {
        if ($user instanceof Authenticatable) {
            $this->app['auth']->driver($driver)->setUser($user);
        } else {
            $this->app['auth']->driver($driver)->attempt($user);
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
     * (http://laravel.com/docs/ioc)
     *
     * Example
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
     * Inserts record into the database.
     *
     * ``` php
     * <?php
     * $user_id = $I->haveRecord('users', array('name' => 'Davert'));
     * ?>
     * ```
     *
     * @param $tableName
     * @param array $attributes
     * @return mixed
     * @part orm
     */
    public function haveRecord($tableName, $attributes = [])
    {
        try {
            return $this->app['db']->table($tableName)->insertGetId($attributes);
        } catch (\Exception $e) {
            $this->fail("Couldn't insert record into table $tableName: " . $e->getMessage());
        }
    }

    /**
     * Checks that record exists in database.
     *
     * ``` php
     * <?php
     * $I->seeRecord('users', array('name' => 'davert'));
     * ?>
     * ```
     *
     * @param $tableName
     * @param array $attributes
     * @part orm
     */
    public function seeRecord($tableName, $attributes = [])
    {
        $record = $this->findRecord($tableName, $attributes);
        if (!$record) {
            $this->fail("Couldn't find $tableName with " . json_encode($attributes));
        }
        $this->debugSection($tableName, json_encode($record));
    }

    /**
     * Checks that record does not exist in database.
     *
     * ``` php
     * <?php
     * $I->dontSeeRecord('users', array('name' => 'davert'));
     * ?>
     * ```
     *
     * @param $tableName
     * @param array $attributes
     * @part orm
     */
    public function dontSeeRecord($tableName, $attributes = [])
    {
        $record = $this->findRecord($tableName, $attributes);
        $this->debugSection($tableName, json_encode($record));
        if ($record) {
            $this->fail("Unexpectedly managed to find $tableName with " . json_encode($attributes));
        }
    }

    /**
     * Retrieves record from database
     *
     * ``` php
     * <?php
     * $category = $I->grabRecord('users', array('name' => 'davert'));
     * ?>
     * ```
     *
     * @param $tableName
     * @param array $attributes
     * @return mixed
     * @part orm
     */
    public function grabRecord($tableName, $attributes = [])
    {
        return $this->findRecord($tableName, $attributes);
    }

    /**
     * @param $tableName
     * @param array $attributes
     * @return mixed
     */
    protected function findRecord($tableName, $attributes = [])
    {
        $query = $this->app['db']->table($tableName);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }

}
