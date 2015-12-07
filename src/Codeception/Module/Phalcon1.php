<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleException;
use PDOException;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\RouterInterface;
use Phalcon\Mvc\Router\RouteInterface;
use Codeception\Util\ReflectionHelper;
use Phalcon\Mvc\Url;
use Codeception\TestCase;
use Codeception\Configuration;
use Codeception\Lib\Connector\Phalcon as PhalconConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Exception\ModuleConfigException;
use Exception;
use Codeception\Lib\Connector\PhalconMemorySession;

/**
 * This module provides integration with [Phalcon framework](http://www.phalconphp.com/) (1.x).
 * Please try it and leave your feedback.
 *
 * ## Demo Project
 *
 * <https://github.com/phalcon/forum>
 *
 * ## Status
 *
 * * Maintainer: **Serghei Iakovlev**
 * * Stability: **stable**
 * * Contact: sadhooklay@gmail.com
 *
 * ## Example
 *
 *     modules:
 *         enabled:
 *             - Phalcon1:
 *                 bootstrap: 'app/config/bootstrap.php'
 *                 cleanup: true
 *                 savepoints: true
 *
 * ## Config
 *
 * The following configurations are required for this module:
 * * boostrap: the path of the application bootstrap file</li>
 * * cleanup: cleanup database (using transactions)</li>
 * * savepoints: use savepoints to emulate nested transactions</li>
 *
 * The application bootstrap file must return Application object but not call its handle() method.
 *
 * Sample bootstrap (`app/config/bootstrap.php`):
 *
 * ``` php
 * <?php
 * $config = include __DIR__ . "/config.php";
 * include __DIR__ . "/loader.php";
 * $di = new \Phalcon\DI\FactoryDefault();
 * include __DIR__ . "/services.php";
 * return new \Phalcon\Mvc\Application($di);
 * ?>
 * ```
 *
 * ## API
 *
 * * di - `Phalcon\Di\Injectable` instance
 * * client - `BrowserKit` client
 *
 * ## Parts
 *
 * * ORM - include only haveRecord/grabRecord/seeRecord/dontSeeRecord actions
 *
 */
class Phalcon1 extends Framework implements ActiveRecord, PartedModule
{
    protected $config = [
        'bootstrap'  => 'app/config/bootstrap.php',
        'cleanup'    => true,
        'savepoints' => true,
    ];

    /**
     * Phalcon bootstrap file path
     */
    protected $bootstrapFile = null;

    /**
     * Dependency injection container
     * @var DiInterface
     */
    public $di = null;

    /**
     * Phalcon Connector
     * @var PhalconConnector
     */
    public $client;

    /**
     * HOOK: used after configuration is loaded
     *
     * @throws ModuleConfigException
     */
    public function _initialize()
    {
        if (!file_exists($this->bootstrapFile = Configuration::projectDir() . $this->config['bootstrap'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "Bootstrap file does not exist in " . $this->config['bootstrap'] . "\n"
                . "Please create the bootstrap file that returns Application object\n"
                . "And specify path to it with 'bootstrap' config\n\n"
                . "Sample bootstrap: \n\n<?php\n"
                . '$config = include __DIR__ . "/config.php";' . "\n"
                . 'include __DIR__ . "/loader.php";' . "\n"
                . '$di = new \Phalcon\DI\FactoryDefault();' . "\n"
                . 'include __DIR__ . "/services.php";' . "\n"
                . 'return new \Phalcon\Mvc\Application($di);'
            );
        }

        $this->client = new PhalconConnector();
    }

    /**
     * HOOK: before scenario
     *
     * @param TestCase $test
     * @throws ModuleException
     */
    public function _before(TestCase $test)
    {
        $application = require $this->bootstrapFile;
        if (!$application instanceof Injectable) {
            throw new ModuleException(__CLASS__, 'Bootstrap must return \Phalcon\Di\Injectable object');
        }

        $this->di = $application->getDI();

        if (!$this->di instanceof DiInterface) {
            throw new ModuleException(__CLASS__, 'Dependency injector container must implement DiInterface');
        }

        Di::reset();
        Di::setDefault($this->di);

        if ($this->di->has('session')) {
            // Destroy existing sessions of previous tests
            $this->di['session'] = new PhalconMemorySession();
        }

        if ($this->di->has('cookies')) {
            $this->di['cookies']->useEncryption(false);
        }

        if ($this->config['cleanup'] && $this->di->has('db')) {
            if ($this->config['savepoints']) {
                $this->di['db']->setNestedTransactionsWithSavepoints(true);
            }
            $this->di['db']->begin();
        }

        // localize
        $bootstrap = $this->bootstrapFile;
        $this->client->setApplication(function () use ($bootstrap) {
            $currentDi = Di::getDefault();
            $application = require $bootstrap;
            $di = $application->getDI();
            if ($currentDi->has('db')) {
                $di['db'] = $currentDi['db'];
            }
            if ($currentDi->has('session')) {
                $di['session'] = $currentDi['session'];
            }
            if ($di->has('cookies')) {
                $di['cookies']->useEncryption(false);
            }
            return $application;
        });
    }

    /**
     * HOOK: after scenario
     *
     * @param TestCase $test
     */
    public function _after(TestCase $test)
    {
        if ($this->config['cleanup'] && isset($this->di['db'])) {
            while ($this->di['db']->isUnderTransaction()) {
                $level = $this->di['db']->getTransactionLevel();
                try {
                    $this->di['db']->rollback(true);
                } catch (PDOException $e) {
                }
                if ($level == $this->di['db']->getTransactionLevel()) {
                    break;
                }
            }
            $this->di['db']->close();
        }
        $this->di = null;
        Di::reset();

        $_SESSION = $_FILES = $_GET = $_POST = $_COOKIE = $_REQUEST = [];
    }

    public function _parts()
    {
        return ['orm'];
    }

    /**
     * Provides access the Phalcon application object.
     *
     * @see \Codeception\Lib\Connector\Phalcon::getApplication
     * @return \Phalcon\Mvc\Application|\Phalcon\Mvc\Micro|\Phalcon\Cli\Console
     */
    public function getApplication()
    {
        $this->client->getApplication();
    }

    /**
     * Sets value to session. Use for authorization.
     *
     * @param string $key
     * @param mixed $val
     */
    public function haveInSession($key, $val)
    {
        $this->di->get('session')->set($key, $val);
        $this->debugSection('Session', json_encode($this->di['session']->toArray()));
    }

    /**
     * Checks that session contains value.
     * If value is `null` checks that session has key.
     *
     * ``` php
     * <?php
     * $I->seeInSession('key');
     * $I->seeInSession('key', 'value');
     * ?>
     * ```
     *
     * @param string $key
     * @param mixed $value
     */
    public function seeInSession($key, $value = null)
    {
        $this->debugSection('Session', json_encode($this->di['session']->toArray()));

        if (is_array($key)) {
            $this->seeSessionHasValues($key);
            return;
        }

        if (!$this->di['session']->has($key)) {
            $this->fail("No session variable with key '$key'");
        }

        if (is_null($value)) {
            $this->assertTrue($this->di['session']->has($key));
        } else {
            $this->assertEquals($value, $this->di['session']->get($key));
        }
    }

    /**
     * Assert that the session has a given list of values.
     *
     * ``` php
     * <?php
     * $I->seeSessionHasValues(['key1', 'key2']);
     * $I->seeSessionHasValues(['key1' => 'value1', 'key2' => 'value2']);
     * ?>
     * ```
     *
     * @param  array $bindings
     * @return void
     */
    public function seeSessionHasValues(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->seeInSession($value);
            } else {
                $this->seeInSession($key, $value);
            }
        }
    }

    /**
     * Inserts record into the database.
     *
     * ``` php
     * <?php
     * $user_id = $I->haveRecord('Phosphorum\Models\Users', ['name' => 'Phalcon']);
     * $I->haveRecord('Phosphorum\Models\Categories', ['name' => 'Testing']');
     * ?>
     * ```
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
     * @return mixed
     * @part orm
     */
    public function haveRecord($model, $attributes = [])
    {
        $record = $this->getModelRecord($model);
        $res = $record->save($attributes);
        $field = function($field) {
            if (is_array($field)) {
                return implode(', ', $field);
            }

            return $field;
        };

        if (!$res) {
            $messages = $record->getMessages();
            $errors = [];
            foreach ($messages as $message) {
                $errors[] = sprintf('[%s] %s: %s', $message->getType(), $field($message->getField()), $message->getMessage());
            }

            $this->fail(sprintf("Record %s was not saved. Messages: \n%s", $model, implode(PHP_EOL, $errors)));
        }
        $this->debugSection($model, json_encode($record));

        return $this->getModelIdentity($record);
    }

    /**
     * Checks that record exists in database.
     *
     * ``` php
     * <?php
     * $I->seeRecord('Phosphorum\Models\Categories', ['name' => 'Testing']);
     * ?>
     * ```
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
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
     * <?php
     * $I->dontSeeRecord('Phosphorum\Models\Categories', ['name' => 'Testing']);
     * ?>
     * ```
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
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
     * <?php
     * $category = $I->grabRecord('Phosphorum\Models\Categories', ['name' => 'Testing']);
     * ?>
     * ```
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
     * @return mixed
     * @part orm
     */
    public function grabRecord($model, $attributes = [])
    {
        return $this->findRecord($model, $attributes);
    }

    /**
     * Resolves the service based on its configuration from Phalcon's DI container
     * Recommended to use for unit testing.
     *
     * @param string $service    Service name
     * @param array  $parameters Parameters [Optional]
     *
     * @return mixed
     */
    public function grabServiceFromDi($service, array $parameters = [])
    {
        if (!$this->di->has($service)) {
            $this->fail("Service $service is not available in container");
        }

        return $this->di->get($service, $parameters);
    }

    /**
     * Registers a service in the services container and resolve it. This record will be erased after the test.
     * Recommended to use for unit testing.
     *
     * ``` php
     * <?php
     * $filter = $I->haveServiceInDi('filter', ['className' => '\Phalcon\Filter']);
     * ?>
     * ```
     *
     * @param string $name
     * @param mixed $definition
     * @param boolean $shared
     *
     * @return mixed|null
     */
    public function haveServiceInDi($name, $definition, $shared = false)
    {
        try {
            $service = $this->di->set($name, $definition, $shared);
            return $service->resolve();
        } catch (\Exception $e) {
            $this->fail($e->getMessage());

            return null;
        }
    }

    /**
     * Opens web page using route name and parameters.
     *
     * ``` php
     * <?php
     * $I->amOnRoute('posts.create');
     * ?>
     * ```
     *
     * @param $routeName
     * @param array $params
     */
    public function amOnRoute($routeName, $params = [])
    {
        if (!$this->di->has('url')) {
            $this->fail('Unable to resolve "url" service.');
        }

        /** @var Url $url */
        $url = $this->di->getShared('url');

        try {
            $this->amOnPage($url->get(['for' => $routeName], null, true));
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Checks that current url matches route
     *
     * ``` php
     * <?php
     * $I->seeCurrentRouteIs('posts.index');
     * ?>
     * ```
     * @param string $routeName
     */
    public function seeCurrentRouteIs($routeName)
    {
        if (!$this->di->has('url')) {
            $this->fail('Unable to resolve "url" service.');
        }

        /** @var Url $url */
        $url = $this->di->getShared('url');

        try {
            $this->seeCurrentUrlEquals($url->get(['for' => $routeName], null, true));
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
     *
     * @return \Phalcon\Mvc\Model
     */
    protected function findRecord($model, $attributes = [])
    {
        $this->getModelRecord($model);
        $query = [];
        foreach ($attributes as $key => $value) {
            $query[] = "$key = '$value'";
        }
        $squery = implode(' AND ', $query);
        $this->debugSection('Query', $squery);
        return call_user_func_array([$model, 'findFirst'], [$squery]);
    }

    /**
     * Get Model Record
     *
     * @param $model
     *
     * @return \Phalcon\Mvc\Model
     * @throws ModuleException
     */
    protected function getModelRecord($model)
    {
        if (!class_exists($model)) {
            throw new ModuleException(__CLASS__, "Model $model does not exist");
        }

        $record = new $model;
        if (!$record instanceof PhalconModel) {
            throw new ModuleException(__CLASS__, "Model $model is not instance of \\Phalcon\\Mvc\\Model");
        }

        return $record;
    }

    /**
     * Get identity.
     *
     * @param \Phalcon\Mvc\Model $model
     * @return mixed
     */
    protected function getModelIdentity(PhalconModel $model)
    {
        if (property_exists($model, 'id')) {
            return $model->id;
        }

        if (!$this->di->has('modelsMetadata')) {
            return null;
        }

        $primaryKeys = $this->di->get('modelsMetadata')->getPrimaryKeyAttributes($model);

        switch (count($primaryKeys)) {
            case 0:
                return null;
            case 1:
                return $model->{$primaryKeys[0]};
            default:
                return array_intersect_key(get_object_vars($model), array_flip($primaryKeys));
        }
    }

    /**
     * Returns a list of recognized domain names
     *
     * @return array
     */
    protected function getInternalDomains()
    {
        $internalDomains = [$this->getApplicationDomainRegex()];

        /** @var RouterInterface $router */
        $router = $this->di->get('router');

        if ($router instanceof RouterInterface) {
            /** @var RouteInterface[] $routes */
            $routes = $router->getRoutes();

            foreach ($routes as $route) {
                if ($route instanceof RouteInterface) {
                    $hostName = $route->getHostName();
                    if (!empty($hostName)) {
                        $internalDomains[] = '/^' . str_replace('.', '\.', $route->getHostName()) . '$/';
                    }
                }
            }
        }

        return array_unique($internalDomains);
    }

    /**
     * @return string
     */
    private function getApplicationDomainRegex()
    {
        $server = ReflectionHelper::readPrivateProperty($this->client, 'server');
        $domain = $server['HTTP_HOST'];

        return '/^' . str_replace('.', '\.', $domain) . '$/';
    }
}
