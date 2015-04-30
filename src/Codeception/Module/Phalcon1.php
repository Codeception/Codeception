<?php

namespace Codeception\Module;

use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model as PhalconModel;
use Codeception\TestCase;
use Codeception\Configuration;
use Codeception\Util\Connector\Phalcon1 as Phalcon1Connector;
use Codeception\Exception\ModuleConfig;
use Codeception\Step;
use Codeception\Util\Connector\PhalconMemorySession;
use Codeception\Util\Framework;
use Codeception\Util\ActiveRecordInterface;

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
 * class_name: TestGuy
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
 * Stability: **alfa**
 *
 *
 */
class Phalcon1 extends Framework implements ActiveRecordInterface
{
    protected $config = array(
        'bootstrap' => 'app/config/bootstrap.php',
        'cleanup' => true,
        'savepoints' => true,
    );

    /**
     * Phalcon bootstrap file path
     * @var null
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

    public function _initialize()
    {
        if (!file_exists($this->bootstrapFile = Configuration::projectDir() . $this->config['bootstrap'])) {
            throw new ModuleConfig(__CLASS__,
                "Bootstrap file does not exists in ".$this->bootstrapFile."\n".
                "Please create the bootstrap file that return Application object\n".
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

    public function _before(TestCase $test)
    {
        $application = require $this->bootstrapFile;
        if (!$application instanceof Injectable) {
            throw new \Exception('Bootstrap must return \Phalcon\DI\Injectable object');
        }

        $this->di = $application->getDi();
        Di::reset();
        Di::setDefault($this->di);

        if (isset($this->di['session'])) {
            $this->di['session'] = new PhalconMemorySession();
        }

        if (isset($this->di['cookies'])) {
            $this->di['cookies']->useEncryption(false);
        }

        if ($this->config['cleanup'] && isset($this->di['db'])) {
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
            $di = $application->getDi();
            if (isset($currentDi['db'])) {
                $di['db'] = $currentDi['db'];
            }
            if (isset($currentDi['session'])) {
                $di['session'] = $currentDi['session'];
            }
            if (isset($di['cookies'])) {
                $di['cookies']->useEncryption(false);
            }
            return $application;
        });
    }

    public function _after(TestCase $test)
    {
        if ($this->config['cleanup'] && isset($this->di['db'])) {
            while ($this->di['db']->isUnderTransaction()) {
                $level = $this->di['db']->getTransactionLevel();
                try {
                    $this->di['db']->rollback(true);
                } catch (\PDOException $e) {}
                if ($level == $this->di['db']->getTransactionLevel()) {
                    break;
                }
            }
        }
        $this->di = null;
        Di::reset();

        $_SESSION = array();
        $_FILES = array();
        $_GET = array();
        $_POST = array();
        $_COOKIE = array();
        $_REQUEST = array();
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
     * $user_id = $I->haveRecord('Phosphorum\Models\Users', array('name' => 'Phalcon'));
     * $I->haveRecord('Phosphorum\Models\Categories', array('name' => 'Testing')');
     * ?>
     * ```
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
     * @return mixed
     */
    public function haveRecord($model, $attributes = array())
    {
        $record = $this->getModelRecord($model);
        $res = $record->save($attributes);
        if (!$res) {
            $this->fail("Record $model was not saved. Messages: ".implode(', ', $record->getMessages()));
        }
        $this->debugSection($model, json_encode($record));

        return $this->getModelIdentity($record);
    }

    /**
     * Checks that record exists in database.
     *
     * ``` php
     * <?php
     * $I->seeRecord('Phosphorum\Models\Categories', array('name' => 'Testing'));
     * ```
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
     */
    public function seeRecord($model, $attributes = array())
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
     * $I->dontSeeRecord('Phosphorum\Models\Categories', array('name' => 'Testing'));
     * ```
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
     */
    public function dontSeeRecord($model, $attributes = array())
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
     * $category = $I->grabRecord('Phosphorum\Models\Categories', array('name' => 'Testing'));
     * ```
     *
     * @param string $model Model name
     * @param array $attributes Model attributes
     * @return mixed
     */
    public function grabRecord($model, $attributes = array())
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
    protected function findRecord($model, $attributes = array())
    {
        $this->getModelRecord($model);
        $query = array();
        foreach ($attributes as $key => $value) {
            $query[] = "$key = '$value'";
        }
        $query = implode(' AND ', $query);
        $this->debugSection('Query', $query);
        return call_user_func_array(array($model, 'findFirst'), array($query));
    }

    /**
     * Get Model Record
     *
     * @param $model
     *
     * @return \Phalcon\Mvc\Model
     */
    protected function getModelRecord($model)
    {
        if (!class_exists($model)) {
            throw new \RuntimeException("Model $model does not exist");
        }
        $record = new $model;
        if (!$record instanceof PhalconModel) {
            throw new \RuntimeException(sprintf('Model %s is not instance of \Phalcon\Mvc\Model', $model));
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
