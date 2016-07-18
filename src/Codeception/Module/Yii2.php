<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Framework;
use Codeception\Configuration;
use Codeception\Lib\Notification;
use Codeception\Subscriber\ErrorHandler;
use Codeception\TestInterface;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Lib\Connector\Yii2 as Yii2Connector;
use yii\db\ActiveRecordInterface;
use Yii;

/**
 * This module provides integration with [Yii framework](http://www.yiiframework.com/) (2.0).
 * It initializes Yii framework in test environment and provides actions for functional testing.
 *
 * ## Config
 *
 * * `configFile` *required* - the path to the application config file. File should be configured for test environment and return configuration array.
 * * `entryUrl` - initial application url (default: http://localhost/index-test.php).
 * * `entryScript` - front script title (like: index-test.php). If not set - taken from entryUrl.
 * * `cleanup` - (default: true) wrap all database connection inside a transaction and roll it back after the test. Should be disabled for acceptance testing..
 *
 * You can use this module by setting params in your functional.suite.yml:
 *
 * ```yaml
 * class_name: FunctionalTester
 * modules:
 *     enabled:
 *         - Yii2:
 *             configFile: '/path/to/config.php'
 * ```
 *
 * ### Parts
 *
 * * `init` - use module only for initialization (for acceptance tests).
 * * `orm` - include only haveRecord/grabRecord/seeRecord/dontSeeRecord actions
 *
 * ### Example (`functional.suite.yml`)
 *
 * ```yml
 * class_name: FunctionalTester
 * modules:
 *   enabled:
 *      - Yii2:
 *          configFile: 'config/test.php'
 * ```
 *
 * ### Example (`unit.suite.yml`)
 *
 * ```yml
 * class_name: UnitTester
 * modules:
 *   enabled:
 *      - Asserts
 *      - Yii2:
 *          configFile: 'config/test.php'
 *          part: init
 * ```
 *
 * ### Example (`acceptance.suite.yml`)
 *
 * ```yml
 * class_name: AcceptanceTester
 * modules:
 *     enabled:
 *         - WebDriver:
 *             url: http://127.0.0.1:8080/
 *             browser: firefox
 *         - Yii2:
 *             configFile: 'config/test.php'
 *             part: ORM # allow to use AR methods
 *             cleanup: false # don't wrap test in transaction
 *             entryScript: index-test.php
 * ```
 *
 * ## Status
 *
 * Maintainer: **samdark**
 * Stability: **stable**
 *
 */
class Yii2 extends Framework implements ActiveRecord, PartedModule
{
    /**
     * Application config file must be set.
     * @var array
     */
    protected $config = [
        'cleanup' => false,
        'entryScript' => '',
        'entryUrl' => 'http://localhost/index-test.php',
    ];

    protected $requiredFields = ['configFile'];
    protected $transaction;

    public $app;

    public function _initialize()
    {
        if (!is_file(Configuration::projectDir() . $this->config['configFile'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "The application config file does not exist: {$this->config['configFile']}"
            );
        }
        $this->defineConstants();
    }

    protected function revertErrorHandler()
    {
        $handler = new ErrorHandler();
        set_error_handler(array($handler, 'errorHandler'));
    }

    public function _before(TestInterface $test)
    {
        $entryUrl = $this->config['entryUrl'];
        $entryFile = $this->config['entryScript'] ?: basename($entryUrl);
        $entryScript = $this->config['entryScript'] ?: parse_url($entryUrl, PHP_URL_PATH);

        $this->client = new Yii2Connector();
        $this->client->defaultServerVars = [
            'SCRIPT_FILENAME' => $entryFile,
            'SCRIPT_NAME' => $entryScript,
            'SERVER_NAME' => parse_url($entryUrl, PHP_URL_HOST),
            'SERVER_PORT' =>  parse_url($entryUrl, PHP_URL_PORT) ?: '80',
        ];
        $this->client->defaultServerVars['HTTPS'] = parse_url($entryUrl, PHP_URL_SCHEME) === 'https';
        $this->client->restoreServerVars();
        $this->client->configFile = Configuration::projectDir().$this->config['configFile'];
        $this->app = $this->client->getApplication();
        $this->revertErrorHandler();

        if ($this->config['cleanup'] && isset($this->app->db)) {
            $this->transaction = $this->app->db->beginTransaction();
        }
    }

    public function _after(\Codeception\TestInterface $test)
    {
        $_SESSION = [];
        $_FILES = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];
        if ($this->transaction && $this->config['cleanup']) {
            $this->transaction->rollback();
        }

        \yii\web\UploadedFile::reset();

        if (\Yii::$app->has('session', true)) {
            \Yii::$app->session->close();
        }
        parent::_after($test);
    }

    public function _parts()
    {
        return ['orm', 'init'];
    }

    /**
     * Authorizes user on a site without submitting login form.
     * Use it for fast pragmatic authorization in functional tests.
     *
     * ```php
     * <?php
     * // User is found by id
     * $I->amLoggedAs(1);
     *
     * // User object is passed as parameter
     * $admin = \app\models\User::findByUsername('admin');
     * $I->amLoggedAs($admin);
     * ```
     * Requires `user` component to be enabled and configured.
     *
     * @param $user
     * @throws ModuleException
     */
    public function amLoggedAs($user)
    {
        if (!Yii::$app->has('user')) {
            throw new ModuleException($this, 'User component is not loaded');
        }
        if ($user instanceof \yii\web\IdentityInterface) {
            $identity = $user;
        } else {
            // class name implementing IdentityInterface
            $identityClass = Yii::$app->user->identityClass;
            $identity = call_user_func([$identityClass, 'findIdentity'], $user);
        }
        Yii::$app->user->login($identity);
    }

    /**
     * Inserts record into the database.
     *
     * ``` php
     * <?php
     * $user_id = $I->haveRecord('app\models\User', array('name' => 'Davert'));
     * ?>
     * ```
     *
     * @param $model
     * @param array $attributes
     * @return mixed
     * @part orm
     */
    public function haveRecord($model, $attributes = [])
    {
        /** @var $record \yii\db\ActiveRecord  * */
        $record = $this->getModelRecord($model);
        $record->setAttributes($attributes, false);
        $res = $record->save(false);
        if (!$res) {
            $this->fail("Record $model was not saved");
        }
        return $record->primaryKey;
    }

    /**
     * Checks that record exists in database.
     *
     * ``` php
     * $I->seeRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param $model
     * @param array $attributes
     * @part orm
     */
    public function seeRecord($model, $attributes = [])
    {
        $record = $this->findRecord($model, $attributes);
        if (!$record) {
            $this->fail("Couldn't find $model with " . json_encode($attributes));
        }
        $this->debugSection($model, json_encode($record));
    }

    /**
     * Checks that record does not exist in database.
     *
     * ``` php
     * $I->dontSeeRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param $model
     * @param array $attributes
     * @part orm
     */
    public function dontSeeRecord($model, $attributes = [])
    {
        $record = $this->findRecord($model, $attributes);
        $this->debugSection($model, json_encode($record));
        if ($record) {
            $this->fail("Unexpectedly managed to find $model with " . json_encode($attributes));
        }
    }

    /**
     * Retrieves record from database
     *
     * ``` php
     * $category = $I->grabRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param $model
     * @param array $attributes
     * @return mixed
     * @part orm
     */
    public function grabRecord($model, $attributes = [])
    {
        return $this->findRecord($model, $attributes);
    }

    protected function findRecord($model, $attributes = [])
    {
        $this->getModelRecord($model);
        return call_user_func([$model, 'find'])
            ->where($attributes)
            ->one();
    }

    protected function getModelRecord($model)
    {
        if (!class_exists($model)) {
            throw new \RuntimeException("Model $model does not exist");
        }
        $record = new $model;
        if (!$record instanceof ActiveRecordInterface) {
            throw new \RuntimeException("Model $model is not implement interface \\yii\\db\\ActiveRecordInterface");
        }
        return $record;
    }

    /**
     * Converting $page to valid Yii 2 URL
     *
     * Allows input like:
     *
     * ```php
     * $I->amOnPage(['site/view','page'=>'about']);
     * $I->amOnPage('index-test.php?site/index');
     * $I->amOnPage('http://localhost/index-test.php?site/index');
     * ```
     *
     * @param $page string|array parameter for \yii\web\UrlManager::createUrl()
     */
    public function amOnPage($page)
    {
        if (is_array($page)) {
            $page = Yii::$app->getUrlManager()->createUrl($page);
        }
        parent::amOnPage($page);
    }

    /**
     * Getting domain regex from rule host template
     *
     * @param string $template
     * @return string
     */
    private function getDomainRegex($template)
    {
        if (preg_match('#https?://(.*)#', $template, $matches)) {
            $template = $matches[1];
        }
        $parameters = [];
        if (strpos($template, '<') !== false) {
            $template = preg_replace_callback(
                '/<(?:\w+):?([^>]+)?>/u',
                function ($matches) use (&$parameters) {
                    $key = '#' . count($parameters) . '#';
                    $parameters[$key] = isset($matches[1]) ? $matches[1] : '\w+';
                    return $key;
                },
                $template
            );
        }
        $template = preg_quote($template);
        $template = strtr($template, $parameters);
        return '/^' . $template . '$/u';
    }

    /**
     * Returns a list of regex patterns for recognized domain names
     *
     * @return array
     */
    public function getInternalDomains()
    {
        $domains = [$this->getDomainRegex(Yii::$app->urlManager->hostInfo)];

        if (Yii::$app->urlManager->enablePrettyUrl) {
            foreach (Yii::$app->urlManager->rules as $rule) {
                /** @var \yii\web\UrlRule $rule */
                if (isset($rule->host)) {
                    $domains[] = $this->getDomainRegex($rule->host);
                }
            }
        }
        return array_unique($domains);
    }

    private function defineConstants()
    {
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        defined('YII_ENV') or define('YII_ENV', 'test');
        defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);

        if (YII_ENV !== 'test') {
            Notification::warning("YII_ENV is not set to `test`, please add \n\n`define(\'YII_ENV\', \'test\');`\n\nto bootstrap file", 'Yii Framework');
        }
    }
}
