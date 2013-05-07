<?php
namespace Codeception\Module;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Client;
use Illuminate\Auth\UserInterface;

class Laravel4 extends \Codeception\Util\Framework {

	public function _initialize()
	{
		//make sure we have a trailing slash (may not be necessary)
		$projectDir =  rtrim(\Codeception\Configuration::projectDir(),DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		//--> copied from './bootstrap/autoload'
		if (file_exists($compiled = $projectDir.'/compiled.php'))
		{
			require $compiled;
		}
		\Illuminate\Support\ClassLoader::register();
		if (is_dir($workbench = $projectDir.'workbench'))
		{
			\Illuminate\Workbench\Starter::start($workbench);
		}
		//--> end copied from './bootstrap/autoload'

		//--> copied from './app/tests/TestCase.php
		$unitTesting = true;
		$testEnvironment = 'testing';
		$app = require $projectDir.'bootstrap/start.php';
		//--> end copied from './app/tests/TestCase.php

		$this->kernel = $app;
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
	 * Assert whether the client was redirected to a given URI.
	 *
	 * @param  string  $uri
	 * @param  array   $with
	 * @return void
	 */
	public function seeLocatedAt($uri, $with = array())
	{
		$response = $this->client->getResponse();

		$this->assertInstanceOf('Illuminate\Http\Response', $response);

		$this->assertEquals($this->kernel['url']->to($uri), $response->headers->get('Location'));

		$this->seeSessionHasValues($with);
	}

	/**
	 * Assert whether the client was redirected to a given route.
	 *
	 * @param  string  $name
	 * @param  array   $with
	 * @return void
	 */
	public function seeRoutePage($name, $with = array())
	{
		$this->amLocatedAt($this->kernel['url']->route($name), $with);
	}

	/**
	 * Assert whether the client was redirected to a given action.
	 *
	 * @param  string  $name
	 * @param  array   $with
	 * @return void
	 */
	public function seeActionPage($name, $with = array())
	{
		$this->amLocatedAt($this->kernel['url']->action($name), $with);
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
				$this->seeSessionHas($value);
			}
			else
			{
				$this->seeSessionHas($key, $value);
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
		return $this->seeSessionHas('errors');
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

}
