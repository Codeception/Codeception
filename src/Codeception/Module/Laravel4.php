<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfig;
use Codeception\Lib\Connector\Laravel4 as LaravelConnector;
use Codeception\Lib\Connector\LaravelMemorySessionHandler;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Subscriber\ErrorHandler;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 *
 * This module allows you to run functional tests for Laravel 4.
 * Please try it and leave your feedback.
 * The original author of this module is Davert.
 *
 * ## Demo Project
 *
 * <https://github.com/Codeception/sample-l4-app>
 *
 * ## Status
 *
 * * Maintainer: **Jan-Henk Gerritsen**
 * * Stability: **stable**
 * * Contact: janhenkgerritsen@gmail.com
 *
 * ## Config
 *
 * * cleanup: `boolean`, default `true` - all db queries will be run in transaction, which will be rolled back at the end of test.
 * * unit: `boolean`, default `true` - Laravel will run in unit testing mode.
 * * environment: `string`, default `testing` - When running in unit testing mode, we will set a different environment.
 * * start: `string`, default `bootstrap/start.php` - Relative path to start.php config file.
 * * root: `string`, default ` ` - Root path of our application.
 * * filters: `boolean`, default: `false` - enable or disable filters for testing.
 *
 * ## API
 *
 * * app - `Illuminate\Foundation\Application` instance
 * * client - `BrowserKit` client
 *
 */
class Laravel4 extends Framework implements ActiveRecord
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
     * @param array $config
     */
    public function __construct($config = null)
    {
        $this->config = array_merge(
            array(
                'cleanup' => true,
                'unit' => true,
                'environment' => 'testing',
                'start' => 'bootstrap' . DIRECTORY_SEPARATOR . 'start.php',
                'root' => '',
                'filters' => false,
            ),
            (array) $config
        );

        $projectDir = explode('workbench', \Codeception\Configuration::projectDir())[0];
        $projectDir .= $this->config['root'];

        $this->config['project_dir'] = $projectDir;
        $this->config['start_file'] = $projectDir . $this->config['start'];

        parent::__construct();
    }

    /**
     * Initialize hook.
     */
    public function _initialize()
    {
        $this->checkStartFileExists();
        $this->registerAutoloaders();
        $this->revertErrorHandler();
        $this->client = new LaravelConnector($this);
    }

    /**
     * Before hook.
     *
     * @param \Codeception\TestCase $test
     * @throws ModuleConfig
     */
    public function _before(\Codeception\TestCase $test)
    {
        if ($this->config['filters']) {
            $this->haveEnabledFilters();
        }

        if ($this->app['db'] && $this->cleanupDatabase()) {
            $this->app['db']->beginTransaction();
        }
    }

    /**
     * After hook.
     *
     * @param \Codeception\TestCase $test
     */
    public function _after(\Codeception\TestCase $test)
    {
        if ($this->app['db'] && $this->cleanupDatabase()) {
            $this->app['db']->rollback();
        }
    }

    /**
     * Before step hook.
     *
     * @param \Codeception\Step $step
     */
    public function _beforeStep(\Codeception\Step $step)
    {
        parent::_beforeStep($step);

        $session = $this->app['session.store'];
        if (! $session->isStarted()) {
            $session->start();
        }
    }

    /**
     * After step hook.
     *
     * @param \Codeception\Step $step
     */
    public function _afterStep(\Codeception\Step $step)
    {
        parent::_beforeStep($step);

        $this->app['session.store']->save();
        Facade::clearResolvedInstances();
    }

    /**
     * Make sure the Laravel start file exists.
     *
     * @throws ModuleConfig
     */
    public function checkStartFileExists()
    {
        $startFile = $this->config['start_file'];

        if (! file_exists($startFile)) {
            throw new ModuleConfig(
                $this, "Laravel bootstrap start.php file not found in $startFile.\nPlease provide a valid path to it using 'start' config param. "
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

        if (is_dir($workbench = $this->config['project_dir'] . 'workbench')) {
            \Illuminate\Workbench\Starter::start($workbench);
        }
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
     * Should database cleanup be performed?
     *
     * @return bool
     */
    protected function cleanupDatabase()
    {
        if (! $this->databaseTransactionsSupported()) {
            return false;
        }

        return $this->config['cleanup'];
    }

    /**
     * Does the Laravel installation support database transactions?
     *
     * @return bool
     */
    protected function databaseTransactionsSupported()
    {
        return version_compare(\Illuminate\Foundation\Application::VERSION, '4.0.6', '>=');
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
     * Enable Laravel filters for next requests.
     */
    public function haveEnabledFilters()
    {
        $this->app['router']->enableFilters();
    }

    /**
     * Disable Laravel filters for next requests.
     */
    public function haveDisabledFilters()
    {
        $this->app['router']->disableFilters();
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
     * @param $route
     * @param array $params
     */
    public function amOnRoute($route, $params = [])
    {
        $domain = $this->app['router']->getRoutes()->getByName($route)->domain();
        $absolute = ! is_null($domain);

        $url = $this->app['url']->route($route, $params, $absolute);
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
        $domain = $this->app['router']->getRoutes()->getByAction($action)->domain();
        $absolute = ! is_null($domain);

        $url = $this->app['url']->action($action, $params, $absolute);
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
     * Assert that Session has error messages
     * The seeSessionHasValues cannot be used, as Message bag Object is returned by Laravel4
     *
     * Useful for validation messages and generally messages array
     *  e.g.
     *  return `Redirect::to('register')->withErrors($validator);`
     *
     * Example of Usage
     *
     * ``` php
     * <?php
     * $I->seeSessionErrorMessage(array('username'=>'Invalid Username'));
     * ?>
     * ```
     * @param array $bindings
     * @deprecated
     */
    public function seeSessionErrorMessage(array $bindings)
    {
        $this->seeFormHasErrors(); //check if  has errors at all
        $this->seeFormErrorMessages($bindings);
    }

    /**
     * Assert that the session has errors bound.
     *
     * ``` php
     * <?php
     * $I->seeSessionHasErrors();
     * ?>
     * ```
     *
     * @return bool
     * @deprecated
     */
    public function seeSessionHasErrors()
    {
        $this->seeFormHasErrors();
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
        $viewErrorBag = $this->app['view']->shared('errors');
        $this->assertTrue(count($viewErrorBag) > 0);
    }

    /**
     * Assert that specific form error messages are set in the view.
     *
     * Useful for validation messages and generally messages array
     *  e.g.
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
     * Takes either `UserInterface` instance or array of credentials.
     *
     * @param  \Illuminate\Auth\UserInterface|array $user
     * @param  string $driver
     * @return void
     */
    public function amLoggedAs($user, $driver = null)
    {
        if ($user instanceof \Illuminate\Auth\UserInterface) {
            $this->app['auth']->driver($driver)->login($user);
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
     */
    public function haveRecord($tableName, $attributes = array())
    {
        $id = $this->app['db']->table($tableName)->insertGetId($attributes);
        if (!$id) {
            $this->fail("Couldn't insert record into table $tableName");
        }
        return $id;
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
     */
    public function seeRecord($tableName, $attributes = array())
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
     */
    public function dontSeeRecord($tableName, $attributes = array())
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
     */
    public function grabRecord($tableName, $attributes = array())
    {
        return $this->findRecord($tableName, $attributes);
    }

    /**
     * @param $tableName
     * @param array $attributes
     * @return mixed
     */
    protected function findRecord($tableName, $attributes = array())
    {
        $query = $this->app['db']->table($tableName);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }
        return $query->first();
    }

    /**
     * Calls an Artisan command and returns output as a string
     *
     * @param string $command       The name of the command as displayed in the artisan command list
     * @param array  $parameters    An associative array of command arguments
     *
     * @return string
     */
    public function callArtisan($command, array $parameters = array())
    {
        $output = new BufferedOutput();

        /** @var \Illuminate\Console\Application $artisan */
        $artisan = $this->app['artisan'];
        $artisan->call($command, $parameters, $output);

        return $output->fetch();
    }

}