<?php

namespace Codeception\Module;

/**
 * This module provides integration with Yii framework (http://www.yiiframework.com/) (1.1.14dev).
 *
  * The following configurations are available for session:
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
 * You need to fix your CHttpRequest (or your component that you use for it) in this way:
 * <ul>
 * <li>Add private property $_headers; in your component</li>
 * <li>Add public method getHeaders() in your component that will simply return $_headers</li>
 * <li>Replace header() function by adding header to your $_headers: $this->_header['yourHeaderName']='Header value';</li>
 * <li>Check your code that it does not use exit(), because then script execution will be interrupted</li>
 * </ul>
 *
 * In CHttpRequest if you use it, do this:
 * - replace header('Location: '.$url, true, $statusCode); with $this->_headers['Location'] = $url;
 * - replace Yii::app()->end(); with Yii::app()->end(0,false); // false - do not call exit()
 *
 * You can test this module by creating new empty Yii application and creating this scenario:
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
 * You must see "OK" and that all steps are marked with asteriks (*).
 * Do not foget that afte adding module in your functional.suite.yml you must run codeception "build" command.
 */
class Yii1 extends \Codeception\Util\Framework implements \Codeception\Util\FrameworkInterface
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

	public function _initialize()
	{
		$this->appSettings = require_once($this->config['appPath']);
		$this->client = new \Codeception\Util\Connector\Yii1();
		$this->client->url = $this->config['url'];
		$this->client->appSettings = $this->appSettings;
		$this->client->appPath = $this->config['appPath'];
	}

	public function _before(\Codeception\TestCase $test)
	{
	}

	public function _after(\Codeception\TestCase $test)
	{
		$_SESSION = array();
		$_GET     = array();
		$_POST    = array();
		$_COOKIE  = array();
		$_REQUEST = array();
		parent::_after($test);
	}

}