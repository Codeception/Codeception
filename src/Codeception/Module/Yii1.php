<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfig;
use Yii;

/**
 * This module provides integration with Yii framework (http://www.yiiframework.com/) (1.1.14dev).
 *
 * The following configurations are available for this module:
 * <ul>
 * <li>appPath - full path to the application, include index.php</li>
 * <li>url - full url to the index.php entry script</li>
 * </ul>
 * In your index.php you must return array with correct configuration for the application:
 *
 * For the simple created yii application index.php will be like this:
 * <pre>
 * // change the following paths if necessary
 * $yii=dirname(__FILE__).'/../yii/framework/yii.php';
 * $config=dirname(__FILE__).'/protected/config/main.php';
 *
 * // remove the following lines when in production mode
 * defined('YII_DEBUG') or define('YII_DEBUG',true);
 * // specify how many levels of call stack should be shown in each log message
 * defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
 * require_once($yii);
 * return array(
 *		'class' => 'CWebApplication',
 *		'config' => $config,
 * );
 * </pre>
 *
 * You can use this module by setting params in your functional.suite.yml:
 * <pre>
 * class_name: TestGuy
 * modules:
 *     enabled: [Yii1, TestHelper]
 *     config:
 *         Yii1:
 *             appPath: '/path/to/index.php'
 *             url: 'http://localhost/path/to/index.php'
 * </pre>
 *
 *
 * You will also need to install [Codeception-Yii Bridge](https://github.com/Codeception/YiiBridge)
 * which include component wrappers for testing.
 *
 * When you are done, you can test this module by creating new empty Yii application and creating this scenario:
 * <pre>
 * $I = new TestGuy($scenario);
 * $I->wantTo('Test index page');
 * $I->amOnPage('/index.php');
 * $I->see('My Web Application','#header #logo');
 * $I->click('Login');
 * $I->see('Login','h1');
 * $I->see('Username');
 * $I->fillField('#LoginForm_username','demo');
 * $I->fillField('#LoginForm_password','demo');
 * $I->click('#login-form input[type="submit"]');
 * $I->seeLink('Logout (demo)');
 * $I->click('Logout (demo)');
 * $I->seeLink('Login');
 * </pre>
 * Then run codeception: php codecept.phar --steps run functional
 * You must see "OK" and that all steps are marked with asterisk (*).
 * Do not forget that after adding module in your functional.suite.yml you must run codeception "build" command.
 *
 * @property Codeception\Lib\Connector\Yii1 $client
 */
class Yii1 extends \Codeception\Lib\Framework
{

	/**
	 * Application path and url must be set always
	 * @var array
	 */
	protected $requiredFields = array('appPath','url');

	/**
	 * Application settings array('class'=>'YourAppClass','config'=>'YourAppArrayConfig');
	 * @var array
	 */
	private $appSettings;

	private $_appConfig;

	public function _initialize()
	{
        if (!file_exists($this->config['appPath'])) {
            throw new ModuleConfig(__CLASS__,
                "Couldn't load application config file {$this->config['appPath']}\n".
                "Please provide application bootstrap file configured for testing"
            );
        }

		$this->appSettings = include($this->config['appPath']); //get application settings in the entry script

		// get configuration from array or file
		if (is_array($this->appSettings['config'])) {
			$this->_appConfig = $this->appSettings['config'];
		} else {
            if (!file_exists($this->appSettings['config'])) {
                throw new ModuleConfig(__CLASS__,
                    "Couldn't load configuration file from Yii app file: {$this->appSettings['config']}\n".
                    "Please provide valid 'config' parameter"
                );
            }
			$this->_appConfig = include($this->appSettings['config']);
		}

        defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER', false);
        defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);

		$_SERVER['SCRIPT_NAME'] = parse_url($this->config['url'], PHP_URL_PATH);
		$_SERVER['SCRIPT_FILENAME'] = $this->config['appPath'];

        if (!function_exists('launch_codeception_yii_bridge')) {
            throw new ModuleConfig(__CLASS__,
                "Codeception-Yii Bridge is not launched. In order to run tests you need to install https://github.com/Codeception/YiiBridge".
                "Implement function 'launch_codeception_yii_bridge' to load all Codeception overrides"
            );
        }
        launch_codeception_yii_bridge();

		Yii::$enableIncludePath = false;
		Yii::setApplication(null);
		Yii::createApplication($this->appSettings['class'],$this->_appConfig);
	}

	/*
	 * Create the client connector. Called before each test
	 */
	public function _createClient()
	{
		$this->client = new \Codeception\Lib\Connector\Yii1();
		$this->client->appPath = $this->config['appPath'];
		$this->client->url = $this->config['url'];
		$this->client->appSettings = array(
			'class' => $this->appSettings['class'],
			'config' => $this->_appConfig,
		);
	}

	public function _before(\Codeception\TestCase $test)
	{
		$this->_createClient();
	}

	public function _after(\Codeception\TestCase $test)
	{
		$_SESSION = array();
		$_GET     = array();
		$_POST    = array();
		$_COOKIE  = array();
		$_REQUEST = array();
		Yii::app()->session->close();
		parent::_after($test);
	}

}
