<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\LIb\Connector\PhalconMemorySession;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Step;

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
 *         Phalcon1
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
class Phalcon1 extends Framework implements ActiveRecord, PartedModule
{
    protected $config = [
        'bootstrap'  => 'app/config/bootstrap.php',
        'cleanup'    => true,
        'savepoints' => true,
    ];


    /**
     * @var \Phalcon\DiInterface
     */
    public $di;

    public function _initialize()
    {
        if (!file_exists(\Codeception\Configuration::projectDir() . $this->config['bootstrap'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "Bootstrap file does not exist in " . $this->config['bootstrap'] . "\n" .
                "Please create the bootstrap file that returns Application object\n" .
                "And specify path to it with 'bootstrap' config\n\n" .
                "Sample bootstrap: \n\n<?php\n" .
                '$config = include __DIR__ . "/config.php";' . "\n" .
                'include __DIR__ . "/loader.php";' . "\n" .
                '$di = new \Phalcon\DI\FactoryDefault();' . "\n" .
                'include __DIR__ . "/services.php";' . "\n" .
                'return new \Phalcon\Mvc\Application($di);'
            );
        }
        $this->client = new \Codeception\Lib\Connector\Phalcon1();

    }

    public function _before(\Codeception\TestCase $test)
    {
        $bootstrap = \Codeception\Configuration::projectDir() . $this->config['bootstrap'];

        $application = require $bootstrap;
        if (!$application instanceof \Phalcon\DI\Injectable) {
            throw new \Exception('Bootstrap must return \Phalcon\DI\Injectable object');
        }

        $this->di = $application->getDi();
        \Phalcon\DI::reset();
        \Phalcon\DI::setDefault($this->di);

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

        $this->client->setApplication(
            function () use ($bootstrap) {
                $currentDi = \Phalcon\DI::getDefault();
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
            }
        );
    }

    public function _after(\Codeception\TestCase $test)
    {
        if ($this->config['cleanup'] && isset($this->di['db'])) {
            while ($this->di['db']->isUnderTransaction()) {
                $level = $this->di['db']->getTransactionLevel();
                try {
                    $this->di['db']->rollback(true);
                } catch (\PDOException $e) {
                }
                if ($level == $this->di['db']->getTransactionLevel()) {
                    break;
                }
            }
        }
        $this->di = null;
        \Phalcon\DI::reset();

        $_SESSION = [];
        $_FILES = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];
    }

    public function _parts()
    {
        return ['orm'];
    }

    /**
     * Sets value to session. Use for authorization.
     *
     * @param $key
     * @param $val
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
     * @param $key
     * @param null $value
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
     * @param $model
     * @param array $attributes
     * @return mixed
     * @part orm
     */
    public function haveRecord($model, $attributes = [])
    {
        $record = $this->getModelRecord($model);
        $res = $record->save($attributes);
        if (!$res) {
            $this->fail("Record $model was not saved. Messages: " . implode(', ', $record->getMessages()));
        }
        $this->debugSection($model, json_encode($record));

        $reflectedProperty = new \ReflectionProperty(get_class($record), 'id');
        $reflectedProperty->setAccessible(true);
        return $reflectedProperty->getValue($record);
    }

    /**
     * Checks that record exists in database.
     *
     * ``` php
     * $I->seeRecord('Phosphorum\Models\Categories', array('name' => 'Testing'));
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
     * $I->dontSeeRecord('Phosphorum\Models\Categories', array('name' => 'Testing'));
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
     * $category = $I->grabRecord('Phosphorum\Models\Categories', array('name' => 'Testing'));
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
        $query = [];
        foreach ($attributes as $key => $value) {
            $query[] = "$key = '$value'";
        }
        $query = implode(' AND ', $query);
        $this->debugSection('Query', $query);
        return call_user_func_array([$model, 'findFirst'], [$query]);
    }

    protected function getModelRecord($model)
    {
        if (!class_exists($model)) {
            throw new \RuntimeException("Model $model does not exist");
        }
        $record = new $model;
        if (!$record instanceof \Phalcon\Mvc\Model) {
            throw new \RuntimeException("Model $model is not instance of \Phalcon\Mvc\Model");
        }
        return $record;
    }
}
