<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Connector\Laravel4 as LaravelConnector;
use Codeception\Lib\Connector\LaravelMemorySessionHandler;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Lib\ModuleContainer;
use Codeception\Subscriber\ErrorHandler;
use Illuminate\Auth\UserInterface;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

/**
 *
 * This module allows you to run functional tests for Laravel 4.
 * Module is very fresh and should be improved with Laravel testing capabilities.
 * Please try it and leave your feedbacks. If you want to maintain it - connect Codeception team.
 *
 * Uses 'bootstrap/start.php' to launch.
 *
 * ## Demo Project
 *
 * <https://github.com/Codeception/sample-l4-app>
 *
 * ## Status
 *
 * * Maintainer: **Davert**
 * * Stability: **stable**
 * * Contact: davert.codeception@mailican.com
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
 * * kernel - `Illuminate\Foundation\Application` instance
 * * client - `BrowserKit` client
 *
 */
class Laravel4 extends Framework implements ActiveRecord, PartedModule
{
    /**
     * @var \Illuminate\Foundation\Application
     */
    public $kernel;

    protected $config = [];

    public function __construct(ModuleContainer $container, $config = null)
    {
        $this->config = array_merge(
            [
                'cleanup'     => true,
                'unit'        => true,
                'environment' => 'testing',
                'start'       => 'bootstrap' . DIRECTORY_SEPARATOR . 'start.php',
                'root'        => '',
                'filters'     => false,
            ],
            (array)$config
        );

        parent::__construct($container, null);
    }

    public function _initialize()
    {
        $app = $this->getApplication();
        $this->kernel = $app;
        $this->client = new LaravelConnector($app);
        $this->revertErrorHandler();

    }

    public function _parts()
    {
        return ['framework', 'orm'];
    }

    protected function revertErrorHandler()
    {
        $handler = new ErrorHandler();
        set_error_handler([$handler, 'errorHandler']);
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->kernel = $this->getApplication();
        $this->kernel->boot();
        $this->kernel->setRequestForConsoleEnvironment();

        $this->client = new LaravelConnector($this->kernel);
        $this->client->followRedirects(true);

        if ($this->config['filters']) {
            $this->haveEnabledFilters();
        }

        if ($this->transactionCleanup()) {
            $this->kernel['db']->beginTransaction();
        }
    }

    public function _after(\Codeception\TestCase $test)
    {
        if ($this->transactionCleanup()) {
            $this->kernel['db']->rollback();
        }

        if ($this->kernel['auth']) {
            $this->kernel['auth']->logout();
        }

        if ($this->kernel['cache']) {
            $this->kernel['cache']->flush();
        }

        if ($this->kernel['session']) {
            $this->kernel['session']->flush();
        }

        // disconnect from DB to prevent "Too many connections" issue
        if ($this->kernel['db']) {
            $this->kernel['db']->disconnect();
        }
    }

    /**
     * Enable Laravel filters for next requests.
     */
    public function haveEnabledFilters()
    {
        $this->kernel['router']->enableFilters();
    }

    /**
     * Disable Laravel filters for next requests.
     */
    public function haveDisabledFilters()
    {
        $this->kernel['router']->disableFilters();
    }

    protected function transactionCleanup()
    {
        return $this->config['cleanup'] and $this->kernel['db'] and $this->expectedLaravelVersion(4.1);
    }

    protected function expectedLaravelVersion($ver)
    {
        return floatval(\Illuminate\Foundation\Application::VERSION) >= floatval($ver);
    }

    /**
     * Opens web page using route name and parameters.
     *
     * ```php
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
        $url = $this->kernel['url']->route($route, $params);
        $this->amOnPage($url);
    }

    /**
     * Opens web page by action name
     *
     * ```php
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
        $url = $this->kernel['url']->action($action, $params);
        $this->amOnPage($url);
    }

    /**
     * Checks that current url matches route
     *
     * ```php
     * <?php
     * $I->seeCurrentRouteIs('posts.index');
     * ?>
     * ```
     * @param $route
     * @param array $params
     */
    public function seeCurrentRouteIs($route, $params = [])
    {
        $this->seeCurrentUrlEquals($this->kernel['url']->route($route, $params, false));
    }

    /**
     * Checks that current url matches action
     *
     * ```php
     * <?php
     * $I->seeCurrentActionIs('PostsController@index');
     * ?>
     * ```
     *
     * @param $action
     * @param array $params
     */
    public function seeCurrentActionIs($action, $params = [])
    {
        $this->seeCurrentUrlEquals($this->kernel['url']->action($action, $params, false));
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
            $this->assertTrue($this->kernel['session']->has($key));
        } else {
            $this->assertEquals($value, $this->kernel['session']->get($key));
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
     */
    public function seeSessionErrorMessage(array $bindings)
    {
        $this->seeSessionHasErrors(); //check if  has errors at all
        $errorMessageBag = $this->kernel['session']->get('errors');
        foreach ($bindings as $key => $value) {
            $this->assertEquals($value, $errorMessageBag->first($key));
        }
    }

    /**
     * Assert that the session has errors bound.
     *
     * @return bool
     */
    public function seeSessionHasErrors()
    {
        $this->seeInSession('errors');
    }

    /**
     * Set the currently logged in user for the application.
     * Takes either `UserInterface` instance or array of credentials.
     *
     * @param  \Illuminate\Auth\UserInterface|array $user
     * @param  string $driver
     * @return void
     * @part framework
     */
    public function amLoggedAs($user, $driver = null)
    {
        if ($user instanceof \Illuminate\Auth\UserInterface) {
            $this->kernel['auth']->driver($driver)->setUser($user);
        } else {
            $this->kernel['auth']->driver($driver)->attempt($user);
        }
    }

    /**
     * Logs user out
     * @part framework
     */
    public function logout()
    {
        $this->kernel['auth']->logout();
    }

    /**
     * Checks that user is authenticated
     * @part framework
     */
    public function seeAuthentication()
    {
        $this->assertTrue($this->kernel['auth']->check(), 'User is not logged in');
    }

    /**
     * Check that user is not authenticated
     */
    public function dontSeeAuthentication()
    {
        $this->assertFalse($this->kernel['auth']->check(), 'User is logged in');
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
     * @part framework
     */
    public function grabService($class)
    {
        return $this->kernel[$class];
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
     * @param $model
     * @param array $attributes
     * @return mixed
     * @part orm
     * @part framework
     */
    public function haveRecord($model, $attributes = [])
    {
        $id = $this->kernel['db']->table($model)->insertGetId($attributes);
        if (!$id) {
            $this->fail("Couldnt insert record into table $model");
        }
        return $id;
    }

    /**
     * Checks that record exists in database.
     *
     * ``` php
     * $I->seeRecord('users', array('name' => 'davert'));
     * ```
     *
     * @param $model
     * @param array $attributes
     * @part orm
     * @part framework
     */
    public function seeRecord($model, $attributes = [])
    {
        $record = $this->findRecord($model, $attributes);
        if (!$record) {
            $this->fail("Couldn't find $model with " . json_encode($attributes));
        }
        $this->debugSection($model, json_encode($record));
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
     * @param $model
     * @param array $attributes
     * @part orm
     * @part framework
     */
    public function dontSeeRecord($model, $attributes = [])
    {
        $record = $this->findRecord($model, $attributes);
        $this->debugSection($model, json_encode($record));
        if ($record) {
            $this->fail("Unexpectedly managed to find $model with " . json_encode($attributes));
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
     * @param $model
     * @param array $attributes
     * @return mixed
     * @part ORM
     * @part framework
     */
    public function grabRecord($model, $attributes = [])
    {
        return $this->findRecord($model, $attributes);
    }

    protected function findRecord($model, $attributes = [])
    {
        $query = $this->kernel['db']->table($model);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }
        return $query->first();
    }

    /**
     * @return \Illuminate\Foundation\Application
     * @throws \Codeception\Exception\ModuleConfigException
     */
    protected function getApplication()
    {
        $projectDir = explode('workbench', \Codeception\Configuration::projectDir())[0];
        $projectDir .= $this->config['root'];
        require $projectDir . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        \Illuminate\Support\ClassLoader::register();

        if (is_dir($workbench = $projectDir . 'workbench')) {
            \Illuminate\Workbench\Starter::start($workbench);
        }

        $startFile = $projectDir . $this->config['start'];

        if (!file_exists($startFile)) {
            throw new ModuleConfigException(
                $this, "Laravel start.php file not found in $startFile.\nPlease provide a valid path to it using 'start' config param. "
            );
        }

        $unitTesting = $this->config['unit'];
        $testEnvironment = $this->config['environment'];

        $app = require $startFile;
        return $app;
    }


}
