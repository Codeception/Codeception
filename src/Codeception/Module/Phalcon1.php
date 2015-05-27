<?php

namespace Codeception\Module;

use Codeception\Exception\Module;
use PDOException;
use RuntimeException;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model as PhalconModel;
use Codeception\TestCase;
use Codeception\Configuration;
use Codeception\Lib\Connector\Phalcon1 as Phalcon1Connector;
use Codeception\Exception\ModuleConfig;
use Codeception\Step;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Connector\PhalconMemorySession;

/**
 * This module provides integration with [Phalcon framework](http://www.phalconphp.com/) (1.x).
 *
 * ## Demo Project
 *
 * <https://github.com/phalcon/forum>
 *
 * The following configurations are required for this module:
 * <ul>
 * <li>boostrap - the path of the application bootstrap file</li>
 * <li>cleanup - cleanup database (using transactions)</li>
 * <li>savepoints - use savepoints to emulate nested transactions</li>
 * </ul>
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
 * You can use this module by setting params in your functional.suite.yml:
 * <pre>
 * class_name: FunctionalTester
 * modules:
 *     enabled: [FileSystem, TestHelper, Phalcon1]
 *     config:
 *         Phalcon1:
 *             bootstrap: 'app/config/bootstrap.php'
 *             cleanup: true
 *             savepoints: true
 * </pre>
 *
 *
 * ## Status
 *
 * Maintainer: **cujo**
 * Stability: **beta**
 */
class Phalcon1 extends Framework implements ActiveRecord
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
     * Phalcon1 Connector
     * @var Phalcon1Connector
     */
    public $client;

    /**
     * HOOK: used after configuration is loaded
     *
     * @throws ModuleConfig
     */
    public function _initialize()
    {
        if (!file_exists($this->bootstrapFile = Configuration::projectDir() . $this->config['bootstrap'])) {
            throw new ModuleConfig(__CLASS__,
                "Bootstrap file does not exist in ".$this->bootstrapFile."\n".
                "Please create the bootstrap file that returns Application object\n".
                "And specify path to it with 'bootstrap' config\n\n".
                "Sample bootstrap: \n\n<?php\n".
                '$config = include __DIR__ . "/config.php";'."\n".
                'include __DIR__ . "/loader.php";'."\n".
                '$di = new \Phalcon\DI\FactoryDefault();'."\n".
                'include __DIR__ . "/services.php";'."\n".
                'return new \Phalcon\Mvc\Application($di);'
            );
        }

        $this->client = new Phalcon1Connector();
    }

    /**
     * HOOK: before scenario
     *
     * @param TestCase $test
     * @throws \RuntimeException
     * @throws Module
     */
    public function _before(TestCase $test)
    {
        $application = require $this->bootstrapFile;
        if (!$application instanceof Injectable) {
            throw new RuntimeException('Bootstrap must return \Phalcon\Di\Injectable object');
        }

        $this->di = $application->getDI();

        if (!$this->di instanceof DiInterface) {
            throw new Module(__CLASS__, 'Dependency injector container must implement DiInterface');
        }

        Di::reset();
        Di::setDefault($this->di);

        if ($this->di->has('session')) {
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
                } catch (PDOException $e) {}
                if ($level == $this->di['db']->getTransactionLevel()) {
                    break;
                }
            }
        }
        $this->di = null;
        Di::reset();

        $_SESSION = $_FILES = $_GET = $_POST = $_COOKIE = $_REQUEST = [];
    }

    /**
     * Sets value to session. Use for authorization.
     *
     * @param string $key
     * @param mixed $val
     */
    public function haveInSession($key, $val)
    {
        $this->di->get('session')->set($key, (string)$val);
        $this->debugSection('Session', json_encode($this->di['session']->getAll()));
    }

    /**
     * Checks that session contains value.
     * If value is `null` checks that session has key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function seeInSession($key, $value = null)
    {
        $this->debugSection('Session', json_encode($this->di['session']->getAll()));
        if (is_null($value)) {
            $this->assertTrue($this->di['session']->has($key));
            return;
        }
        $this->assertEquals($value, $this->di['session']->get($key));
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
     */
    public function haveRecord($model, $attributes = [])
    {
        $record = $this->getModelRecord($model);
        $res = $record->save($attributes);
        if (!$res) {
            $messages = $record->getMessages();
            $errors = [];
            foreach ($messages as $message) {
                $errors[] = sprintf('[%s] %s: %s', $message->getType(), $message->getField(), $message->getMessage());
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
     * ```
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
     */
    public function seeRecord($model, $attributes = [])
    {
        $record = $this->findRecord($model, $attributes);
        if (!$record) {
            $this->fail("Couldn't find $model with ".json_encode($attributes));
        }
        $this->debugSection($model, json_encode($record));
    }

    /**
     * Checks that record does not exist in database.
     *
     * ``` php
     * <?php
     * $I->dontSeeRecord('Phosphorum\Models\Categories', ['name' => 'Testing']);
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
            $this->fail("Unexpectedly managed to find $model with ".json_encode($attributes));
        }
    }

    /**
     * Retrieves record from database
     *
     * ``` php
     * <?php
     * $category = $I->grabRecord('Phosphorum\Models\Categories', ['name' => 'Testing']);
     * ```
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
     * @return mixed
     */
    public function grabRecord($model, $attributes = [])
    {
        return $this->findRecord($model, $attributes);
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
        $query = implode(' AND ', $query);
        $this->debugSection('Query', $query);
        return call_user_func_array([$model, 'findFirst'], [$query]);
    }

    /**
     * Get Model Record
     *
     * @param $model
     *
     * @return \Phalcon\Mvc\Model
     * @throws \RuntimeException
     */
    protected function getModelRecord($model)
    {
        if (!class_exists($model)) {
            throw new RuntimeException("Model $model does not exist");
        }
        $record = new $model;
        if (!$record instanceof PhalconModel) {
            throw new RuntimeException(sprintf('Model %s is not instance of \Phalcon\Mvc\Model', $model));
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
}
