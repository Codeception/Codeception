<?php
namespace Codeception\Module;

use Codeception\Codecept;
use Codeception\Subscriber\ErrorHandler;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\Client;
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
 * * cleanup: true - all db queries will be run in transaction, which will be rolled back at the end of test.
 *
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
class Laravel4 extends \Codeception\Util\Framework
{

    protected $config = array('cleanup' => true);

    public function _initialize()
    {
        $projectDir = \Codeception\Configuration::projectDir();
        require $projectDir . '/vendor/autoload.php';

        \Illuminate\Support\ClassLoader::register();

        if (is_dir($workbench = $projectDir . 'workbench')) {
            \Illuminate\Workbench\Starter::start($workbench);
        }
        $unitTesting = true;
        $testEnvironment = 'testing';
        $app = require $projectDir . 'bootstrap/start.php';
        $this->kernel = $app;

        $this->revertErrorHandler();
    }

    protected function revertErrorHandler()
    {
        $handler = new ErrorHandler();
        set_error_handler(array($handler, 'errorHandler'));
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->client = new Client($this->kernel);
        $this->client->followRedirects(true);
        if ($this->config['cleanup'] and $this->expectedLaravelVersion(4.1)) {
            $this->kernel['db']->beginTransaction();
        }
    }

    public function _after(\Codeception\TestCase $test)
    {
        if ($this->config['cleanup'] and $this->expectedLaravelVersion(4.1)) {
            $this->kernel['db']->rollback();
        }
        $this->kernel->shutdown();
    }

    protected function expectedLaravelVersion($ver)
    {
        return floatval(\Illuminate\Foundation\Application::VERSION) >= floatval($ver);
    }

    public function _beforeStep(\Codeception\Step $step)
    {
        // saving referer for redirecting back
        $headers = $this->kernel->request->headers;
        if (!$this->client->getHistory()->isEmpty()) {
            $headers->set('referer', $this->client->getHistory()->current()->getUri());
        }
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
     * @param array $bindings
     *
     * Assert that Session has error messages
     *
     * The seeSessionHasValues cannot be used, as Message bag Object is returned by Laravel4
     *
     * Useful for validation messages and generally messages array
     *  e.g.
     *  return Redirect::to('register')->withErrors($validator);
     *
     * Example of Usage
     *
     * $I->seeSessionErrorMessage(array('username'=>'Invalid Username'));
     *
     */

    public function seeSessionErrorMessage($bindings){


        $this->seeSessionHasErrors(); //check if  has errors at all

        $errorMessageBag = $this->kernel['session']->get('errors');

        foreach($bindings as $key => $value){

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
            $this->fail("Couldn't find $model with ".json_encode($attributes));
        }
        $this->debugSection($model, json_encode($record));
    }

    /**
     * Checks that record does not exist in database.
     *
     * ``` php
     * $I->dontSeeRecord('users', array('name' => 'davert'));
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
            $this->fail("Unexpectedly managed to find $model with ".json_encode($attributes));
        }
    }

    /**
     * Retrieves record from database
     *
     * ``` php
     * $category = $I->grabRecord('users', array('name' => 'davert'));
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
        $query = $this->kernel['db']->table[$model];
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }
        return $query->first();
    }


}
