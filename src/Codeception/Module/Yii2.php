<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Connector\Yii2 as Yii2Connector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Lib\Notification;
use Codeception\TestInterface;
use Yii;
use yii\db\ActiveRecordInterface;

/**
 * This module provides integration with [Yii framework](http://www.yiiframework.com/) (2.0).
 * It initializes Yii framework in test environment and provides actions for functional testing.
 *
 * ## Config
 *
 * * `configFile` *required* - the path to the application config file. File should be configured for test environment and return configuration array.
 * * `entryUrl` - initial application url (default: http://localhost/index-test.php).
 * * `entryScript` - front script title (like: index-test.php). If not set - taken from entryUrl.
 * * `transaction` - (default: true) wrap all database connection inside a transaction and roll it back after the test. Should be disabled for acceptance testing..
 * * `cleanup` - (default: true) cleanup fixtures after the test
 *
 * You can use this module by setting params in your functional.suite.yml:
 *
 * ```yaml
 * actor: FunctionalTester
 * modules:
 *     enabled:
 *         - Yii2:
 *             configFile: '/path/to/config.php'
 * ```
 *
 * ### Parts
 *
 * By default all available methods are loaded, but you can specify parts to select only needed actions and avoid conflicts.
 *
 * * `init` - use module only for initialization (for acceptance tests).
 * * `orm` - include only `haveRecord/grabRecord/seeRecord/dontSeeRecord` actions.
 * * `fixtures` - use fixtures inside tests with `haveFixtures/grabFixture/grabFixtures` actions.
 * * `email` - include email actions `seeEmailsIsSent/grabLastSentEmail/...`
 *
 * ### Example (`functional.suite.yml`)
 *
 * ```yaml
 * actor: FunctionalTester
 * modules:
 *   enabled:
 *      - Yii2:
 *          configFile: 'config/test.php'
 * ```
 *
 * ### Example (`unit.suite.yml`)
 *
 * ```yaml
 * actor: UnitTester
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
 * ```yaml
 * actor: AcceptanceTester
 * modules:
 *     enabled:
 *         - WebDriver:
 *             url: http://127.0.0.1:8080/
 *             browser: firefox
 *         - Yii2:
 *             configFile: 'config/test.php'
 *             part: ORM # allow to use AR methods
 *             transaction: false # don't wrap test in transaction
 *             cleanup: false # don't cleanup the fixtures
 *             entryScript: index-test.php
 * ```
 *
 * ## Fixtures
 *
 * This module allows to use [fixtures](http://www.yiiframework.com/doc-2.0/guide-test-fixtures.html) inside a test. There are two options for that.
 * Fixtures can be loaded using [haveFixtures](#haveFixtures) method inside a test:
 *
 * ```php
 * <?php
 * $I->haveFixtures(['posts' => PostsFixture::className()]);
 * ```
 *
 * or, if you need to load fixtures before the test, you
 * can specify fixtures with `_fixtures` method of a testcase:
 *
 * ```php
 * <?php
 * // inside Cest file or Codeception\TestCase\Unit
 * public function _fixtures()
 * {
 *     return ['posts' => PostsFixture::className()]
 * }
 * ```
 *
 * ## URL
 * This module provide to use native URL formats of Yii2 for all codeception commands that use url for work.
 * This commands allows input like:
 *
 * ```php
 * <?php
 * $I->amOnPage(['site/view','page'=>'about']);
 * $I->amOnPage('index-test.php?site/index');
 * $I->amOnPage('http://localhost/index-test.php?site/index');
 * $I->sendAjaxPostRequest(['/user/update', 'id' => 1], ['UserForm[name]' => 'G.Hopper');
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
    const TEST_FIXTURES_METHOD = '_fixtures';

    /**
     * Application config file must be set.
     * @var array
     */
    protected $config = [
        'cleanup'     => true,
        'transaction' => null,
        'entryScript' => '',
        'entryUrl'    => 'http://localhost/index-test.php',
    ];

    protected $requiredFields = ['configFile'];
    protected $transaction;

    /**
     * @var \yii\base\Application
     */
    public $app;

    /**
     * @var Yii2Connector\FixturesStore[]
     */
    public $loadedFixtures = [];

    public function _initialize()
    {
        if ($this->config['transaction'] === null) {
            $this->config['transaction'] = $this->config['cleanup'];
        }

        if (!is_file(Configuration::projectDir() . $this->config['configFile'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "The application config file does not exist: " . Configuration::projectDir() . $this->config['configFile']
            );
        }
        $this->defineConstants();
    }

    public function _before(TestInterface $test)
    {
        $entryUrl = $this->config['entryUrl'];
        $entryFile = $this->config['entryScript'] ?: basename($entryUrl);
        $entryScript = $this->config['entryScript'] ?: parse_url($entryUrl, PHP_URL_PATH);

        $this->client = new Yii2Connector();
        $this->client->defaultServerVars = [
            'SCRIPT_FILENAME' => $entryFile,
            'SCRIPT_NAME'     => $entryScript,
            'SERVER_NAME'     => parse_url($entryUrl, PHP_URL_HOST),
            'SERVER_PORT'     => parse_url($entryUrl, PHP_URL_PORT) ?: '80',
        ];
        $this->client->defaultServerVars['HTTPS'] = parse_url($entryUrl, PHP_URL_SCHEME) === 'https';
        $this->client->restoreServerVars();
        $this->client->configFile = Configuration::projectDir() . $this->config['configFile'];
        $this->app = $this->client->getApplication();

        // load fixtures before db transaction
        if ($test instanceof \Codeception\Test\Cest) {
            $this->loadFixtures($test->getTestClass());
        } else {
            $this->loadFixtures($test);
        }

        if ($this->config['transaction']
            && $this->app->has('db')
            && $this->app->db instanceof \yii\db\Connection
        ) {
            $this->transaction = $this->app->db->beginTransaction();
            $this->debugSection('Database', 'Transaction started');
        }
    }

    /**
     * load fixtures before db transaction
     *
     * @param mixed $test instance of test class
     */
    private function loadFixtures($test)
    {
        if (empty($this->loadedFixtures)
            && method_exists($test, self::TEST_FIXTURES_METHOD)
        ) {
            $this->haveFixtures(call_user_func([$test, self::TEST_FIXTURES_METHOD]));
        }
    }

    public function _after(TestInterface $test)
    {
        $_SESSION = [];
        $_FILES = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];

        if ($this->config['cleanup']) {
            foreach ($this->loadedFixtures as $fixture) {
                $fixture->unloadFixtures();
            }
            $this->loadedFixtures = [];
        }
      
        if ($this->config['transaction'] && $this->transaction) {
            $this->transaction->rollBack();
            $this->debugSection('Database', 'Transaction cancelled; all changes reverted.');
        }

        if ($this->client) {
            $this->client->resetPersistentVars();
        }

        if (isset(\Yii::$app) && \Yii::$app->has('session', true)) {
            \Yii::$app->session->close();
        }

        // Close connections if exists
        if (isset(\Yii::$app) && \Yii::$app->has('db', true)) {
            \Yii::$app->db->close();
        }

        parent::_after($test);
    }

    public function _parts()
    {
        return ['orm', 'init', 'fixtures', 'email'];
    }

    /**
     * Authorizes user on a site without submitting login form.
     * Use it for fast pragmatic authorization in functional tests.
     *
     * ```php
     * <?php
     * // User is found by id
     * $I->amLoggedInAs(1);
     *
     * // User object is passed as parameter
     * $admin = \app\models\User::findByUsername('admin');
     * $I->amLoggedInAs($admin);
     * ```
     * Requires `user` component to be enabled and configured.
     *
     * @param $user
     * @throws ModuleException
     */
    public function amLoggedInAs($user)
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
     * Creates and loads fixtures from a config.
     * Signature is the same as for `fixtures()` method of `yii\test\FixtureTrait`
     *
     * ```php
     * <?php
     * $I->haveFixtures([
     *     'posts' => PostsFixture::className(),
     *     'user' => [
     *         'class' => UserFixture::className(),
     *         'dataFile' => '@tests/_data/models/user.php',
     *      ],
     * ]);
     * ```
     *
     * Note: if you need to load fixtures before the test (probably before the cleanup transaction is started;
     * `cleanup` options is `true` by default), you can specify fixtures with _fixtures method of a testcase
     * ```php
     * <?php
     * // inside Cest file or Codeception\TestCase\Unit
     * public function _fixtures(){
     *     return [
     *         'user' => [
     *             'class' => UserFixture::className(),
     *             'dataFile' => codecept_data_dir() . 'user.php'
     *         ]
     *     ];
     * }
     * ```
     * instead of defining `haveFixtures` in Cest `_before`
     *
     * @param $fixtures
     * @part fixtures
     */
    public function haveFixtures($fixtures)
    {
        if (empty($fixtures)) {
            return;
        }
        $fixturesStore = new Yii2Connector\FixturesStore($fixtures);
        $fixturesStore->unloadFixtures();
        $fixturesStore->loadFixtures();
        $this->loadedFixtures[] = $fixturesStore;
    }

    /**
     * Returns all loaded fixtures.
     * Array of fixture instances
     *
     * @part fixtures
     * @return array
     */
    public function grabFixtures()
    {
        return call_user_func_array(
            'array_merge',
            array_map( // merge all fixtures from all fixture stores
                function ($fixturesStore) {
                    return $fixturesStore->getFixtures();
                },
                $this->loadedFixtures
            )
        );
    }

    /**
     * Gets a fixture by name.
     * Returns a Fixture instance. If a fixture is an instance of `\yii\test\BaseActiveFixture` a second parameter
     * can be used to return a specific model:
     *
     * ```php
     * <?php
     * $I->haveFixtures(['users' => UserFixture::className()]);
     *
     * $users = $I->grabFixture('users');
     *
     * // get first user by key, if a fixture is instance of ActiveFixture
     * $user = $I->grabFixture('users', 'user1');
     * ```
     *
     * @param $name
     * @return mixed
     * @throws ModuleException if a fixture is not found
     * @part fixtures
     */
    public function grabFixture($name, $index = null)
    {
        $fixtures = $this->grabFixtures();
        if (!isset($fixtures[$name])) {
            throw new ModuleException($this, "Fixture $name is not loaded");
        }
        $fixture = $fixtures[$name];
        if ($index === null) {
            return $fixture;
        }
        if ($fixture instanceof \yii\test\BaseActiveFixture) {
            return $fixture->getModel($index);
        }
        throw new ModuleException($this, "Fixture $name is not an instance of ActiveFixture and can't be loaded with second parameter");
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
     * Similar to amOnPage but accepts route as first argument and params as second
     *
     * ```
     * $I->amOnRoute('site/view', ['page' => 'about']);
     * ```
     *
     */
    public function amOnRoute($route, array $params = [])
    {
        array_unshift($params, $route);
        $this->amOnPage($params);
    }

    /**
     * To support to use the behavior of urlManager component
     * for the methods like this: amOnPage(), sendAjaxRequest() and etc.
     * @param $method
     * @param $uri
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param null $content
     * @param bool $changeHistory
     * @return mixed
     */
    protected function clientRequest($method, $uri, array $parameters = [], array $files = [], array $server = [], $content = null, $changeHistory = true)
    {
        if (is_array($uri)) {
            $uri = Yii::$app->getUrlManager()->createUrl($uri);
        }
        return parent::clientRequest($method, $uri, $parameters, $files, $server, $content, $changeHistory);
    }

    /**
     * Gets a component from Yii container. Throws exception if component is not available
     *
     * ```php
     * <?php
     * $mailer = $I->grabComponent('mailer');
     * ```
     *
     * @param $component
     * @return mixed
     * @throws ModuleException
     */
    public function grabComponent($component)
    {
        if (!Yii::$app->has($component)) {
            throw new ModuleException($this, "Component $component is not available in current application");
        }
        return Yii::$app->get($component);
    }

    /**
     * Checks that email is sent.
     *
     * ```php
     * <?php
     * // check that at least 1 email was sent
     * $I->seeEmailIsSent();
     *
     * // check that only 3 emails were sent
     * $I->seeEmailIsSent(3);
     * ```
     *
     * @param int $num
     * @throws ModuleException
     * @part email
     */
    public function seeEmailIsSent($num = null)
    {
        if ($num === null) {
            $this->assertNotEmpty($this->grabSentEmails(), 'emails were sent');
            return;
        }
        $this->assertEquals($num, count($this->grabSentEmails()), 'number of sent emails is equal to ' . $num);
    }

    /**
     * Checks that no email was sent
     *
     * @part email
     */
    public function dontSeeEmailIsSent()
    {
        $this->seeEmailIsSent(0);
    }

    /**
     * Returns array of all sent email messages.
     * Each message implements `yii\mail\Message` interface.
     * Useful to perform additional checks using `Asserts` module:
     *
     * ```php
     * <?php
     * $I->seeEmailIsSent();
     * $messages = $I->grabSentEmails();
     * $I->assertEquals('admin@site,com', $messages[0]->getTo());
     * ```
     *
     * @part email
     * @return array
     * @throws ModuleException
     */
    public function grabSentEmails()
    {
        $mailer = $this->grabComponent('mailer');
        if (!$mailer instanceof Yii2Connector\TestMailer) {
            throw new ModuleException($this, "Mailer module is not mocked, can't test emails");
        }
        return $mailer->getSentMessages();
    }

    /**
     * Returns last sent email:
     *
     * ```php
     * <?php
     * $I->seeEmailIsSent();
     * $message = $I->grabLastSentEmail();
     * $I->assertEquals('admin@site,com', $message->getTo());
     * ```
     * @part email
     */
    public function grabLastSentEmail()
    {
        $this->seeEmailIsSent();
        $messages = $this->grabSentEmails();
        return end($messages);
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
    }
}
