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

		return $this->kernel = $app;
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

	//*************************************************
	//--> everything from here on copied from './Illuminate/Foundation/Testing/TestCase.php'
	// Replacing '$this->app() with $this->kernel()
	// UNTESTED!!

	/**
	 * Call the given URI and return the Response.
	 *
	 * @param  string  $method
	 * @param  string  $uri
	 * @param  array   $parameters
	 * @param  array   $files
	 * @param  array   $server
	 * @param  string  $content
	 * @param  bool    $changeHistory
	 * @return \Illuminate\Http\Response
	 */
	public function call()
	{
		call_user_func_array(array($this->client, 'request'), func_get_args());

		return $this->client->getResponse();
	}

	/**
	 * Call the given HTTPS URI and return the Response.
	 *
	 * @param  string  $method
	 * @param  string  $uri
	 * @param  array   $parameters
	 * @param  array   $files
	 * @param  array   $server
	 * @param  string  $content
	 * @param  bool    $changeHistory
	 * @return \Illuminate\Http\Response
	 */
	public function callSecure()
	{
		$parameters = func_get_args();

		$parameters[1] = 'https://localhost/'.ltrim($parameters[1], '/');

		return call_user_func_array(array($this, 'call'), $parameters);
	}

	/**
	 * Call a controller action and return the Response.
	 *
	 * @param  string  $method
	 * @param  string  $action
	 * @param  array   $wildcards
	 * @param  array   $parameters
	 * @param  array   $files
	 * @param  array   $server
	 * @param  string  $content
	 * @param  bool    $changeHistory
	 * @return \Illuminate\Http\Response
	 */
	public function action($method, $action, $wildcards = array(), $parameters = array(), $files = array(), $server = array(), $content = null, $changeHistory = true)
	{
		$uri = $this->kernel['url']->action($action, $wildcards, false);

		return $this->call($method, $uri, $parameters, $files, $server, $content, $changeHistory);
	}

	/**
	 * Call a named route and return the Response.
	 *
	 * @param  string  $method
	 * @param  string  $name
	 * @param  array   $routeParameters
	 * @param  array   $parameters
	 * @param  array   $files
	 * @param  array   $server
	 * @param  string  $content
	 * @param  bool    $changeHistory
	 * @return \Illuminate\Http\Response
	 */
	public function route($method, $name, $routeParameters = array(), $parameters = array(), $files = array(), $server = array(), $content = null, $changeHistory = true)
	{
		$uri = $this->kernel['url']->route($name, $routeParameters, false);

		return $this->call($method, $uri, $parameters, $files, $server, $content, $changeHistory);
	}

	/**
	 * Assert that the client response has an OK status code.
	 *
	 * @return void
	 */
	public function assertResponseOk()
	{
		return $this->assertTrue($this->client->getResponse()->isOk());
	}

	/**
	 * Assert that the response view has a given piece of bound data.
	 *
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function assertViewHas($key, $value = null)
	{
		if (is_array($key)) return $this->assertViewHasAll($key);

		$response = $this->client->getResponse()->original;

		if (is_null($value))
		{
			$this->assertArrayHasKey($key, $response->getData());
		}
		else
		{
			$this->assertEquals($value, $response->$key);
		}
	}

	/**
	 * Assert that the view has a given list of bound data.
	 *
	 * @param  array  $bindings
	 * @return void
	 */
	public function assertViewHasAll(array $bindings)
	{
		foreach ($bindings as $key => $value)
		{
			if (is_int($key))
			{
				$this->assertViewHas($value);
			}
			else
			{
				$this->assertViewHas($key, $value);
			}
		}
	}

	/**
	 * Assert whether the client was redirected to a given URI.
	 *
	 * @param  string  $uri
	 * @param  array   $with
	 * @return void
	 */
	public function amRedirectedTo($uri, $with = array())
	{
		$response = $this->client->getResponse();

		$this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);

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
	public function amRedirectedToRoute($name, $with = array())
	{
		$this->amRedirectedTo($this->kernel['url']->route($name), $with);
	}

	/**
	 * Assert whether the client was redirected to a given action.
	 *
	 * @param  string  $name
	 * @param  array   $with
	 * @return void
	 */
	public function amRedirectedToAction($name, $with = array())
	{
		$this->amRedirectedTo($this->kernel['url']->action($name), $with);
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

	/**
	 * Seed a given database connection.
	 *
	 * @param  string  $class
	 * @return void
	 */
	public function seed($class = 'DatabaseSeeder')
	{
		$this->kernel[$class]->run();
	}

}