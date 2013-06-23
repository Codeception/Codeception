<?php

namespace Codeception\Module;

use Yii;
use Codeception\Util\Framework;
use Codeception\Exception\ModuleConfig;

/**
 * This module provides integration with Yii framework (http://www.yiiframework.com/) (2.0).
 *
 * The following configurations are required for this module:
 * <ul>
 * <li>entryScript - the path of the entry script</li>
 * <li>url - the URL of the entry script</li>
 * </ul>
 *
 * The entry script must return the application configuration array.
 *
 * You can use this module by setting params in your functional.suite.yml:
 * <pre>
 * class_name: TestGuy
 * modules:
 *     enabled: [FileSystem, TestHelper, Yii2]
 *     config:
 *         Yii2:
 *             entryScript: '/path/to/index.php'
 *             url: 'http://localhost/path/to/index.php'
 * </pre>
 */
class Yii2 extends Framework
{
	/**
	 * Application path and url must be set always
	 * @var array
	 */
	protected $requiredFields = array('entryScript', 'url');

	public function _before(\Codeception\TestCase $test)
	{
		if (empty($this->config['entryScript'])) {
			throw new ModuleConfig(__CLASS__, "Missing required config: entryScript");
		}
		if (empty($this->config['url'])) {
			throw new ModuleConfig(__CLASS__, "Missing required config: url");
		}
		if (!is_file($this->config['entryScript'])) {
			throw new ModuleConfig(__CLASS__, "The entry script file does not exist: {$this->config['entryScript']}");
		}

		$this->client = new \Codeception\Util\Connector\Yii2();
		$this->client->entryScript = realpath($this->config['entryScript']);
		$this->client->url = $this->config['url'];
	}

	public function _after(\Codeception\TestCase $test)
	{
		$_SESSION = array();
		$_FILES = array();
		$_GET = array();
		$_POST = array();
		$_COOKIE = array();
		$_REQUEST = array();
		if (Yii::$app) {
			Yii::$app->session->close();
		}
		parent::_after($test);
	}
}
