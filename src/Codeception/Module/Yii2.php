<?php

namespace Codeception\Module;

use Yii;
use Codeception\Lib\Framework;
use Codeception\Exception\ModuleConfig;

/**
 * This module provides integration with [Yii framework](http://www.yiiframework.com/) (2.0).
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
 *             configFile: '/path/to/config.php'
 * </pre>
 *
 * ## Status
 *
 * Maintainer: **qiangxue**
 * Stability: **beta**
 *
 */
class Yii2 extends Framework
{
	/**
	 * Application config file must be set.
	 * @var array
	 */
	protected $requiredFields = array('configFile');

	public function _before(\Codeception\TestCase $test)
	{
		if (empty($this->config['configFile'])) {
			throw new ModuleConfig(__CLASS__, "Missing required config: configFile");
		}
		if (!is_file($this->config['configFile'])) {
			throw new ModuleConfig(__CLASS__, "The application config file does not exist: {$this->config['configFile']}");
		}

		$this->client = new \Codeception\Lib\Connector\Yii2();
		$this->client->configFile = realpath($this->config['configFile']);
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
