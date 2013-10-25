<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfig;
use Codeception\Util\Connector\PhalconMemorySession;

/**
 * This module provides integration with [Phalcon framework](http://www.phalconphp.com/) (1.x).
 *
 * The following configurations are required for this module:
 * <ul>
 * <li>application - the path of the application bootstrap file</li>
 * <li>cleanup - cleanup database (using transactions)</li>
 * </ul>
 *
 * The application bootstrap file must return Application object but not call its handle() method.
 *
 * You can use this module by setting params in your functional.suite.yml:
 * <pre>
 * class_name: TestGuy
 * modules:
 *     enabled: [FileSystem, TestHelper, Phalcon1]
 *     config:
 *         Phalcon1
 *             application: 'config/application.php'
 *             cleanup: true
 * </pre>
 *
 * ## Status
 *
 * Maintainer: **cujo**
 * Stability: **alfa**
 *
 */
class Phalcon1 extends \Codeception\Util\Framework
{
    protected $config = array(
        'application' => 'config/application.php',
        'cleanup' => true,
    );


    /**
     * @var \Phalcon\DiInterface
     */
    public $di;

    public function _initialize()
    {
        if (!file_exists(\Codeception\Configuration::projectDir() . $this->config['application'])) {
            throw new ModuleConfig(__CLASS__,
                "Bootstrap file does not exists in ".$this->config['application']."\n".
                "Please create the bootstrap file that return Application object\n".
                "And specify path to it with 'application' config\n\n".
                "Sample bootstrap: \n\n<?php\n".
                '$config = include __DIR__ . "/config.php";'."\n".
                'include __DIR__ . "/loader.php";'."\n".
                '$di = new \Phalcon\DI\FactoryDefault();'."\n".
                'include __DIR__ . "/services.php";'."\n".
                'return new \Phalcon\Mvc\Application($di);'
            );
        }
        $this->client = new \Codeception\Util\Connector\Phalcon1();

    }

    public function _before(\Codeception\TestCase $test)
    {
        $bootstrap = \Codeception\Configuration::projectDir() . $this->config['application'];
        $this->client->setApplication(function () use ($bootstrap) {
            $currentDi = \Phalcon\DI::getDefault();
            $application = require $bootstrap;
            $di = $application->getDi();
            if (isset($currentDi['db'])) {
                $di['db'] = $currentDi['db'];
            }
            if (isset($currentDi['session'])) {
                $di['session'] = $currentDi['session'];
            }
            return $application;
        });

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

        if ($this->config['cleanup'] && isset($this->di['db'])) {
            $this->di['db']->setNestedTransactionsWithSavepoints(true);
            $this->di['db']->begin();
        }
    }

    public function _after(\Codeception\TestCase $test)
    {
        if ($this->config['cleanup'] && isset($this->di['db'])) {
            if ($this->di['db']->isUnderTransaction()) {
                $this->di['db']->rollback();
            }
        }

        $this->di = null;
        \Phalcon\DI::reset();
    }

    public function haveInSession($key, $val)
    {
        $this->di->get('session')->set($key, $val);
    }

    public function haveRecord($model, $attributes = array())
    {
        $record = $this->getModelRecord($model);
        $res = $record->save($attributes);
        if (!$res) {
            $this->fail("Record $model was not saved. Messages: ".implode(', ', $record->getMessages()));
        
        }
        $this->debugSection($model, json_encode($record));
        return $record->id;
    }

    public function seeRecord($model, $attributes = array())
    {
        $this->getModelRecord($model);
        $query = array();
        foreach ($attributes as $key => $value) {
            $query []= "$key = '$value'";
        }
        $query = implode(' AND ', $query);
        $this->debugSection('Query', $query);
        $record = call_user_func_array(array($model, 'findFirst'), array($query));
        if (!$record) {
            $this->fail("Couldn't find $model by query: '$query'");
        }
        $this->debugSection($model, json_encode($record));
    }

    public function grabRecord($model, $attributes = array())
    {
        
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
