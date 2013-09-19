<?php
namespace Codeception\Module;

use Codeception\Codecept;
use Codeception\Subscriber\ErrorHandler;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\Client;
use Illuminate\Auth\UserInterface;

/**
 *
 * This module allows you to run functional tests for Laravel 4.
 * Module is very fresh and should be improved with Laravel testing capabilities.
 * Please try it and leave your feedbacks. If you want to maintin it - connect Codeception team.
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

class Laravel4 extends \Codeception\Util\Framework {

	public function _initialize()
	{
        $projectDir =  \Codeception\Configuration::projectDir();
        require $projectDir.'/vendor/autoload.php';

        \Illuminate\Support\ClassLoader::register();

		if (is_dir($workbench = $projectDir.'workbench'))
		{
			\Illuminate\Workbench\Starter::start($workbench);
		}
		$unitTesting = true;
		$testEnvironment = 'testing';
		$app = require $projectDir.'bootstrap/start.php';
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
	}

	public function _after(\Codeception\TestCase $test)
	{
		$this->kernel->shutdown();
	}

	/**
	 * Assert that the session has a given list of values.
	 *
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function seeInSession($key, $value = null)
	{
		if (is_array($key)) return $this->seeSessionHasValues($key);

		if (is_null($value))
		{
			$this->assertTrue($this->kernel['session']->has($key));
		}
		else
		{
			$this->assertEquals($value, $this->kernel['session']->get($key));
		}

	}

	/**
	 * Assert that the session has a given list of values.
	 *
	 * @param  array  $bindings
	 * @return void
	 */
	public function seeSessionHasValues(array $bindings)
	{
		foreach ($bindings as $key => $value)
		{
			if (is_int($key))
			{
				$this->seeInSession($value);
			}
			else
			{
				$this->seeInSession($key, $value);
			}
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
	 * @param  \Illuminate\Auth\UserInterface  $user
	 * @param  string  $driver
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
	 * @param  string  $class
	 * @return mixed
	 */
	public function grabService($class)
	{
		return $this->kernel[$class];
	}

}
