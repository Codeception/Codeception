<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Connector\Yii2 as Yii2Connector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\TestInterface;
use Codeception\Util\Debug;
use Yii;
use yii\base\Event;
use yii\db\ActiveRecordInterface;
use yii\db\Connection;
use yii\db\QueryInterface;
use yii\db\Transaction;

/**
 * This module provides integration with [Yii framework](http://www.yiiframework.com/) (2.0).
 * It initializes Yii framework in test environment and provides actions for functional testing.
 * ## Application state during testing
 * This section details what you can expect when using this module.
 * * You will get a fresh application in `\Yii::$app` at the start of each test (available in the test and in `_before()`).
 * * Inside your test you may change application state; however these changes will be lost when doing a request if you have enabled `recreateApplication`.
 * * When executing a request via one of the request functions the `request` and `response` component are both recreated.
 * * After a request the whole application is available for inspection / interaction.
 * * You may use multiple database connections, each will use a separate transaction; to prevent accidental mistakes we
 * will warn you if you try to connect to the same database twice but we cannot reuse the same connection.
 *
 * ## Config
 *
 * * `configFile` *required* - the path to the application config file. File should be configured for test environment and return configuration array.
 * * `entryUrl` - initial application url (default: http://localhost/index-test.php).
 * * `entryScript` - front script title (like: index-test.php). If not set - taken from entryUrl.
 * * `transaction` - (default: true) wrap all database connection inside a transaction and roll it back after the test. Should be disabled for acceptance testing..
 * * `cleanup` - (default: true) cleanup fixtures after the test
 * * `ignoreCollidingDSN` - (default: false) When 2 database connections use the same DSN but different settings an exception will be thrown, set this to true to disable this behavior.
 * * `fixturesMethod` - (default: _fixtures) Name of the method used for creating fixtures.
 * * `responseCleanMethod` - (default: clear) Method for cleaning the response object. Note that this is only for multiple requests inside a single test case.
 * Between test casesthe whole application is always recreated
 * * `requestCleanMethod` - (default: recreate) Method for cleaning the request object. Note that this is only for multiple requests inside a single test case.
 * Between test cases the whole application is always recreated
 * * `recreateComponents` - (default: []) Some components change their state making them unsuitable for processing multiple requests. In production this is usually
 * not a problem since web apps tend to die and start over after each request. This allows you to list application components that need to be recreated before each request.
 * As a consequence, any components specified here should not be changed inside a test since those changes will get regarded.
 * You can use this module by setting params in your functional.suite.yml:
 * * `recreateApplication` - (default: false) whether to recreate the whole application before each request
 * You can use this module by setting params in your functional.suite.yml:
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
 * @property \Codeception\Lib\Connector\Yii2 $client
 */
class Yii2 extends Framework implements ActiveRecord, PartedModule
{
    /**
     * Application config file must be set.
     * @var array
     */
    protected $config = [
        'fixturesMethod' => '_fixtures',
        'cleanup'     => true,
        'ignoreCollidingDSN' => false,
        'transaction' => null,
        'entryScript' => '',
        'entryUrl'    => 'http://localhost/index-test.php',
        'responseCleanMethod' => Yii2Connector::CLEAN_CLEAR,
        'requestCleanMethod' => Yii2Connector::CLEAN_RECREATE,
        'recreateComponents' => []
    ];

    protected $requiredFields = ['configFile'];

    /**
     * @var array Array of Transaction objects indexed by a string key
     */
    private $transactions = [];
    /**
     * @var \PDO[] Array of PDO objects indexed by a string key
     */
    private $pdoCache = [];
    /**
     * @var string[] Array of cache keys indexes by their DSN
     */
    private $dsnCache = [];

    /**
     * @var Yii2Connector\FixturesStore[]
     */
    public $loadedFixtures = [];

    /**
     * @var array The contents of $_SERVER upon initialization of this object.
     * This is only used to restore it upon object destruction.
     * It MUST not be used anywhere else.
     */
    private $server;

    public function _initialize()
    {
        if ($this->config['transaction'] === null) {
            $this->config['transaction'] = $this->backupConfig['transaction'] = $this->config['cleanup'];
        }

        $this->defineConstants();
        $this->server = $_SERVER;
        $this->initServerGlobal();
    }


    /**
     * Module configuration changed inside a test.
     * We always re-create the application.
     */
    protected function onReconfigure()
    {
        parent::onReconfigure();
        $this->client->resetApplication();
        $this->configureClient($this->config);
        $this->client->startApp();
    }

    /**
     * Adds the required server params.
     * Note this is done separately from the request cycle since someone might call
     * `Url::to` before doing a request, which would instantiate the request component with incorrect server params.
     */
    private function initServerGlobal()
    {

        $entryUrl = $this->config['entryUrl'];
        $entryFile = $this->config['entryScript'] ?: basename($entryUrl);
        $entryScript = $this->config['entryScript'] ?: parse_url($entryUrl, PHP_URL_PATH);
        $_SERVER = array_merge($_SERVER, [
            'SCRIPT_FILENAME' => $entryFile,
            'SCRIPT_NAME' => $entryScript,
            'SERVER_NAME' => parse_url($entryUrl, PHP_URL_HOST),
            'SERVER_PORT' => parse_url($entryUrl, PHP_URL_PORT) ?: '80',
            'HTTPS' => parse_url($entryUrl, PHP_URL_SCHEME) === 'https'
        ]);
    }

    protected function validateConfig()
    {
        parent::validateConfig();
        if (!is_file(Configuration::projectDir() . $this->config['configFile'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "The application config file does not exist: " . Configuration::projectDir() . $this->config['configFile']
            );
        }

        if (!in_array($this->config['responseCleanMethod'], Yii2Connector::CLEAN_METHODS)) {
            throw new ModuleConfigException(
                __CLASS__,
                "The response clean method must be one of: " . implode(", ", Yii2Connector::CLEAN_METHODS)
            );
        }

        if (!in_array($this->config['requestCleanMethod'], Yii2Connector::CLEAN_METHODS)) {
            throw new ModuleConfigException(
                __CLASS__,
                "The response clean method must be one of: " . implode(", ", Yii2Connector::CLEAN_METHODS)
            );
        }
    }

    protected function configureClient(array $settings)
    {
        $settings['configFile'] = Configuration::projectDir() . $settings['configFile'];

        foreach ($settings as $key => $value) {
            if (property_exists($this->client, $key)) {
                $this->client->$key = $value;
            }
        }
        $this->client->resetApplication();
    }

    /**
     * Instantiates the client based on module configuration
     */
    protected function recreateClient()
    {
        $entryUrl = $this->config['entryUrl'];
        $entryFile = $this->config['entryScript'] ?: basename($entryUrl);
        $entryScript = $this->config['entryScript'] ?: parse_url($entryUrl, PHP_URL_PATH);

        $this->client = new Yii2Connector([
            'SCRIPT_FILENAME' => $entryFile,
            'SCRIPT_NAME' => $entryScript,
            'SERVER_NAME' => parse_url($entryUrl, PHP_URL_HOST),
            'SERVER_PORT' => parse_url($entryUrl, PHP_URL_PORT) ?: '80',
            'HTTPS' => parse_url($entryUrl, PHP_URL_SCHEME) === 'https'
        ]);

        $this->configureClient($this->config);
    }

    public function _before(TestInterface $test)
    {
        $this->recreateClient();
        $this->client->startApp();

        // load fixtures before db transaction
        if ($test instanceof \Codeception\Test\Cest) {
            $this->loadFixtures($test->getTestClass());
        } else {
            $this->loadFixtures($test);
        }

        $this->startTransactions();
    }

    /**
     * load fixtures before db transaction
     *
     * @param mixed $test instance of test class
     */
    private function loadFixtures($test)
    {
        $this->debugSection('Fixtures', 'Loading fixtures');
        /** @var Connection[] $connections */
        $connections = [];
        // Register event handler.
        Event::on(Connection::class, Connection::EVENT_AFTER_OPEN, function (Event $event) use (&$connections) {
            $this->debugSection('Fixtures', 'Opened database connection: ' . $event->sender->dsn);
            $connections[] = $event->sender;
        });
        if (empty($this->loadedFixtures)
            && method_exists($test, $this->_getConfig('fixturesMethod'))
        ) {
            $this->haveFixtures(call_user_func([$test, $this->_getConfig('fixturesMethod')]));
        }

        Event::offAll();
        // Close all connections so they get properly reopened after the transaction handler has been attached.
        foreach ($connections as $connection) {
            $this->debugSection('Fixtures', 'Closing database connection: ' . $connection->dsn);
            $connection->close();
        }
        $this->debugSection('Fixtures', 'Done');
    }

    public function _after(TestInterface $test)
    {
        $_SESSION = [];
        $_FILES = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];

        $this->rollbackTransactions();

        if ($this->config['cleanup']) {
            foreach ($this->loadedFixtures as $fixture) {
                $fixture->unloadFixtures();
            }
            $this->loadedFixtures = [];
        }

        if ($this->client->getApplication()->has('session', true)) {
            $this->client->getApplication()->session->close();
        }
        parent::_after($test);
    }

    public function connectionOpenHandler(Event $event)
    {
        if ($event->sender instanceof Connection) {
            $connection = $event->sender;
            /*
             * We should check if the known PDO objects are the same, in which case we should reuse the PDO
             * object so only 1 transaction is started and multiple connections to the same database see the
             * same data (due to writes inside a transaction not being visible from the outside).
             *
             */
            $key = md5(json_encode([
                'dsn' => $connection->dsn,
                'user' => $connection->username,
                'pass' => $connection->password,
                'attributes' => $connection->attributes,
                'emulatePrepare' => $connection->emulatePrepare,
                'charset' => $connection->charset
            ]));

            /*
             * If keys match we assume connections are "similar enough".
             */
            if (isset($this->pdoCache[$key])) {
                $connection->pdo = $this->pdoCache[$key];
            }

            if (isset($this->dsnCache[$connection->dsn])
                && $this->dsnCache[$connection->dsn] !== $key
                && !$this->config['ignoreCollidingDSN']
            ) {
                $this->debugSection('WARNING', <<<TEXT
You use multiple connections to the same DSN ({$connection->dsn}) with different configuration.
These connections will not see the same database state since we cannot share a transaction between different PDO
instances.
You can remove this message by adding 'ignoreCollidingDSN = true' in the module configuration.
TEXT
                );
                Debug::pause();
            }

            if (isset($this->transactions[$key])) {
                $this->debugSection('Database', 'Reusing PDO, so no need for a new transaction');
                return;
            }

            $this->debugSection('Database', 'Transaction started for: ' . $connection->dsn);
            $this->transactions[$key] = $connection->beginTransaction();
        }

    }

    protected function startTransactions()
    {
        if ($this->config['transaction']) {
            // This should register handlers that start a transaction whenever a connection opens and add it to the transactions array.
            $this->debug('Transaction', 'Registering connection event handler');
            Event::on(Connection::class, Connection::EVENT_AFTER_OPEN, [$this, 'connectionOpenHandler']);
        }
    }

    protected function rollbackTransactions()
    {
        $this->debugSection('Transaction', 'Rolling back ' . count($this->transactions) . ' transactions');
        Event::off(Connection::class, Connection::EVENT_AFTER_OPEN, [$this, 'connectionOpenHandler']);
        /** @var Transaction $transaction */
        foreach ($this->transactions as $transaction) {
            $transaction->rollBack();
            $this->debugSection('Database', 'Transaction cancelled; all changes reverted.');
        }
        $this->transactions = [];
        $this->pdoCache = [];
        $this->dsnCache = [];
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
        if (!$this->client->getApplication()->has('user')) {
            throw new ModuleException($this, 'User component is not loaded');
        }
        if ($user instanceof \yii\web\IdentityInterface) {
            $identity = $user;
        } else {
            // class name implementing IdentityInterface
            $identityClass = $this->client->getApplication()->user->identityClass;
            $identity = call_user_func([$identityClass, 'findIdentity'], $user);
        }
        $this->client->getApplication()->user->login($identity);
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
        $record = \Yii::createObject($model);
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

    /**
     * @param string $model Class name
     * @param array $attributes
     * @return mixed
     */
    protected function findRecord($model, $attributes = [])
    {
        if (!class_exists($model)) {
            throw new \RuntimeException("Class $model does not exist");
        }
        $rc = new \ReflectionClass($model);
        if ($rc->hasMethod('find')
            && ($findMethod = $rc->getMethod('find'))
            && $findMethod->isStatic()
            && $findMethod->isPublic()
            && $findMethod->getNumberOfRequiredParameters() === 0
        ) {
            $activeQuery = $findMethod->invoke(null);
            if ($activeQuery instanceof QueryInterface) {
                return $activeQuery->andWhere($attributes)->one();
            }

            throw new \RuntimeException("$model::find() must return an instance of yii\db\QueryInterface");

        }
        throw new \RuntimeException("Class $model does not have a public static find() method without required parameters");
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
            $uri = $this->client->getApplication()->getUrlManager()->createUrl($uri);
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
        if (!$this->client->getApplication()->has($component)) {
            throw new ModuleException($this, "Component $component is not available in current application");
        }
        return $this->client->getApplication()->get($component);
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
     * Each message implements `yii\mail\MessageInterface` interface.
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
        $domains = [$this->getDomainRegex($this->client->getApplication()->urlManager->hostInfo)];

        if ($this->client->getApplication()->urlManager->enablePrettyUrl) {
            foreach ($this->client->getApplication()->urlManager->rules as $rule) {
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

    /**
     * Sets a cookie and, if validation is enabled, signs it.
     * @param string $name The name of the cookie
     * @param string $value The value of the cookie
     * @param array $params Additional cookie params like `domain`, `path`, `expires` and `secure`.
     */
    public function setCookie($name, $val, array $params = [])
    {
        // Sign the cookie.
        if ($this->client->getApplication()->request->enableCookieValidation) {
            $val = $this->client->getApplication()->security->hashData(serialize([$name, $val]), $this->client->getApplication()->request->cookieValidationKey);
        }
        parent::setCookie($name, $val, $params);
    }

    /**
     * This function creates the CSRF Cookie.
     * @param string $val The value of the CSRF token
     * @return string[] Returns an array containing the name of the CSRF param and the masked CSRF token.
     */
    public function createAndSetCsrfCookie($val)
    {
        $masked = $this->client->getApplication()->security->maskToken($val);
        $name = $this->client->getApplication()->request->csrfParam;
        $this->setCookie($name, $val);
        return [$name, $masked];
    }

    public function _afterSuite()
    {
        parent::_afterSuite();
        codecept_debug('Suite done, restoring $_SERVER to original');

        $_SERVER = $this->server;
    }

}
