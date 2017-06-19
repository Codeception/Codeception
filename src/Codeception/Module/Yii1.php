<?php
namespace Codeception\Module;

use Codeception\Lib\Framework;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\TestInterface;
use Codeception\Lib\Connector\Yii1 as Yii1Connector;
use Codeception\Util\ReflectionHelper;
use Yii;

/**
 * This module provides integration with [Yii Framework 1.1](http://www.yiiframework.com/doc/guide/).
 *
 * The following configurations are available for this module:
 *
 *  * `appPath` - full path to the application, include index.php</li>
 *  * `url` - full url to the index.php entry script</li>
 *
 * In your index.php you must return an array with correct configuration for the application:
 *
 * For the simple created yii application index.php will be like this:
 *
 * ```php
 * <?php
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
 *        'class' => 'CWebApplication',
 *        'config' => $config,
 * );
 * ```
 *
 * You can use this module by setting params in your `functional.suite.yml`:
 *
 * ```yaml
 * actor: FunctionalTester
 * modules:
 *     enabled:
 *         - Yii1:
 *             appPath: '/path/to/index.php'
 *             url: 'http://localhost/path/to/index.php'
 *         - \Helper\Functional
 * ```
 *
 * You will also need to install [Codeception-Yii Bridge](https://github.com/Codeception/YiiBridge)
 * which include component wrappers for testing.
 *
 * When you are done, you can test this module by creating new empty Yii application and creating this Cept scenario:
 *
 * ```
 * php codecept.phar g:cept functional IndexCept
 * ```
 *
 * and write it as in example:
 *
 * ```php
 * <?php
 * $I = new FunctionalTester($scenario);
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
 * ```
 *
 * Then run codeception: php codecept.phar --steps run functional
 * You must see "OK" and that all steps are marked with asterisk (*).
 * Do not forget that after adding module in your functional.suite.yml you must run codeception "build" command.
 *
 * ### Public Properties
 *
 * `client`: instance of `\Codeception\Lib\Connector\Yii1`
 *
 * ### Parts
 *
 * If you ever encounter error message:
 *
 * ```
 * Yii1 module conflicts with WebDriver
 * ```
 *
 * you should include Yii module partially, with `init` part only
 *
 * * `init`: only initializes module and not provides any actions from it. Can be used for unit/acceptance tests to avoid conflicts.
 *
 * ### Acceptance Testing Example:
 *
 * In `acceptance.suite.yml`:
 *
 * ```yaml
 * class_name: AcceptanceTester
 * modules:
 *     enabled:
 *         - WebDriver:
 *             browser: firefox
 *             url: http://localhost
 *         - Yii1:
 *             appPath: '/path/to/index.php'
 *             url: 'http://localhost/path/to/index.php'
 *             part: init # to not conflict with WebDriver
 *         - \Helper\Acceptance
 * ```
 */
class Yii1 extends Framework implements PartedModule
{

    /**
     * Application path and url must be set always
     * @var array
     */
    protected $requiredFields = ['appPath', 'url'];

    /**
     * Application settings array('class'=>'YourAppClass','config'=>'YourAppArrayConfig');
     * @var array
     */
    private $appSettings;

    private $_appConfig;

    public function _initialize()
    {
        if (!file_exists($this->config['appPath'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "Couldn't load application config file {$this->config['appPath']}\n" .
                "Please provide application bootstrap file configured for testing"
            );
        }

        $this->appSettings = include($this->config['appPath']); //get application settings in the entry script

        // get configuration from array or file
        if (is_array($this->appSettings['config'])) {
            $this->_appConfig = $this->appSettings['config'];
        } else {
            if (!file_exists($this->appSettings['config'])) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "Couldn't load configuration file from Yii app file: {$this->appSettings['config']}\n" .
                    "Please provide valid 'config' parameter"
                );
            }
            $this->_appConfig = include($this->appSettings['config']);
        }

        if (!defined('YII_ENABLE_EXCEPTION_HANDLER')) {
            define('YII_ENABLE_EXCEPTION_HANDLER', false);
        }
        if (!defined('YII_ENABLE_ERROR_HANDLER')) {
            define('YII_ENABLE_ERROR_HANDLER', false);
        }

        $_SERVER['SCRIPT_NAME'] = parse_url($this->config['url'], PHP_URL_PATH);
        $_SERVER['SCRIPT_FILENAME'] = $this->config['appPath'];

        if (!function_exists('launch_codeception_yii_bridge')) {
            throw new ModuleConfigException(
                __CLASS__,
                "Codeception-Yii Bridge is not launched. In order to run tests you need to install "
                . "https://github.com/Codeception/YiiBridge Implement function 'launch_codeception_yii_bridge' to "
                . "load all Codeception overrides"
            );
        }
        launch_codeception_yii_bridge();

        Yii::$enableIncludePath = false;
        Yii::setApplication(null);
        Yii::createApplication($this->appSettings['class'], $this->_appConfig);
    }

    /*
     * Create the client connector. Called before each test
     */
    public function _createClient()
    {
        $this->client = new Yii1Connector();
        $this->client->setServerParameter("HTTP_HOST", parse_url($this->config['url'], PHP_URL_HOST));
        $this->client->appPath = $this->config['appPath'];
        $this->client->url = $this->config['url'];
        $this->client->appSettings = [
            'class'  => $this->appSettings['class'],
            'config' => $this->_appConfig,
        ];
    }

    public function _before(TestInterface $test)
    {
        $this->_createClient();
    }

    public function _after(TestInterface $test)
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];
        Yii::app()->session->close();
        parent::_after($test);
    }

    /**
     * Getting domain regex from rule template and parameters
     *
     * @param string $template
     * @param array $parameters
     * @return string
     */
    private function getDomainRegex($template, $parameters = [])
    {
        if ($host = parse_url($template, PHP_URL_HOST)) {
            $template = $host;
        }
        if (strpos($template, '<') !== false) {
            $template = str_replace(['<', '>'], '#', $template);
        }
        $template = preg_quote($template);
        foreach ($parameters as $name => $value) {
            $template = str_replace("#$name#", $value, $template);
        }
        return '/^' . $template . '$/u';
    }


    /**
     * Returns a list of regex patterns for recognized domain names
     *
     * @return array
     */
    public function getInternalDomains()
    {
        $domains = [$this->getDomainRegex(Yii::app()->request->getHostInfo())];
        if (Yii::app()->urlManager->urlFormat === 'path') {
            $parent = Yii::app()->urlManager instanceof \CUrlManager ? '\CUrlManager' : null;
            $rules = ReflectionHelper::readPrivateProperty(Yii::app()->urlManager, '_rules', $parent);
            foreach ($rules as $rule) {
                if ($rule->hasHostInfo === true) {
                    $domains[] = $this->getDomainRegex($rule->template, $rule->params);
                }
            }
        }
        return array_unique($domains);
    }

    public function _parts()
    {
        return ['init', 'initialize'];
    }
}
