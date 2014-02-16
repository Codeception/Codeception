<?php
namespace Codeception\Module;

/**
 * This module provides integration with [Kohana](http://kohanaframework.org/) v3.
 * Functional tests can be run inside Kohana. All commands of this module are just the same as in other modules that share Framework interface.
 *
 * ## Status
 *
 * * Maintainer: **Nikita Groshin**
 * * Stability: **alpha**
 * * Contact: nike-17@ya.ru
 *
 * ### Installation
 *
 * This module sets $_SERVER['KOHANA_ENV'] = 'testing'
 *
 * 1. Fix your bootstrap/index.php [like this](https://gist.github.com/2043592)
 * 2. You need install this module https://github.com/nike-17/codeception-kohana
 *   or just fix your Cookie class like this https://github.com/nike-17/codeception-kohana/blob/master/classes/cookie.php 
 * 3. if you have some problem pls feel free to ask me nike-17@ya.ru
 *
 * Module is created by [Nikita Groshin](nike-17@ya.ru)
 *
 */

class Kohana extends \Codeception\Lib\Framework {

	public function _initialize() {
		
	}

	public function _before(\Codeception\TestCase $test) {
		$this->client = new \Codeception\Lib\Connector\Kohana();
		$this->client->setIndex('public/index.php');
	}

	public function _after(\Codeception\TestCase $test) {
		$_SESSION = array();
		$_GET = array();
		$_POST = array();
		$_COOKIE = array();
		parent::_after($test);
	}

}