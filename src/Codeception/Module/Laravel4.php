<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfig;
use Codeception\Lib\Connector\LaravelMemorySessionHandler;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Subscriber\ErrorHandler;
use Codeception\Lib\Connector\Laravel4 as LaravelConnector;
use Illuminate\Http\Request;
use Illuminate\Auth\UserInterface;
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
 * * Maintainer: **Jon Phipps, Davert**
 * * Stability: **alpha**
 * * Contact: davert.codeception@mailican.com
 *
 * ## Config
 *
 * * cleanup: `boolean`, default `true` - all db queries will be run in transaction, which will be rolled back at the end of test.
 * * unit: `boolean`, default `true` - Laravel will run in unit testing mode.
 * * environment: `string`, default `testing` - When running in unit testing mode, we will set a different environment.
 * * start: `string`, default `bootstrap/start.php` - Relative path to start.php config file.
 * * root: `string`, default ` ` - Root path of our application.
 *
 * ## API
 *
 * * kernel - `Illuminate\Foundation\Application` instance
 * * client - `BrowserKit` client
 *
 * ## Known Issues
 *
 * When submitting form do not use `Input::all` to pass to store (hope you won't do this anyway).
 * Codeception creates internal form fields, so you get exception trying to save them.
 *
 */
class Laravel4 extends Framework implements ActiveRecord
{
    /**
     * @var \Illuminate\Foundation\Application
     */
    public $kernel;

    protected $config = [];

    public function __construct($config = null)
    {
        $this->config = array_merge(
            array(
                'cleanup' => true,
                'unit' => true,
                'environment' => 'testing',
                'start' => 'bootstrap' . DIRECTORY_SEPARATOR . 'start.php',
                'root' => '',
            ),
            (array)$config
        );

        parent::__construct();
    }

    public function _initialize()
    {
        $app = $this->getApplication();
        $this->kernel = $app;
        $this->client = new LaravelConnector($app);
        $this->revertErrorHandler();
    }

    protected function revertErrorHandler()
    {
        $handler = new ErrorHandler();
        set_error_handler(array($handler, 'errorHandler'));
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->kernel = $this->getApplication();
        $this->client = new LaravelConnector($this->kernel);
        $this->client->followRedirects(true);

        if ($this->transactionCleanup()) {
            $this->kernel['db']->beginTransaction();
        }
//        $this->kernel['router']->enableFilters();
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
     *
     * @param  \Illuminate\Auth\UserInterface $user
     * @param  string $driver
     * @return void
     */
    public function amLoggedAs(UserInterface $user, $driver = null)
    {
        $this->kernel['auth']->driver($driver)->setUser($user);
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
     */
    public function haveRecord($model, $attributes = array())
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
     */
    public function seeRecord($model, $attributes = array())
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
     */
    public function dontSeeRecord($model, $attributes = array())
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
     */
    public function grabRecord($model, $attributes = array())
    {
        return $this->findRecord($model, $attributes);
    }

    protected function findRecord($model, $attributes = array())
    {
        $query = $this->kernel['db']->table($model);
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }
        return $query->first();
    }

    /**
     * @return \Illuminate\Foundation\Application
     * @throws \Codeception\Exception\ModuleConfig
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
            throw new ModuleConfig(
                $this, "Laravel start.php file not found in $startFile.\nPlease provide a valid path to it using 'start' config param. "
            );
        }

        $unitTesting = $this->config['unit'];
        $testEnvironment = $this->config['environment'];

        $app = require $startFile;
        return $app;
    }


}
