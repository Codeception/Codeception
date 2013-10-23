<?php

namespace Codeception\Module;

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
 *             entryScript: 'config/application.php'
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

    private $di;

    public function _before(\Codeception\TestCase $test)
    {
        $this->client = new \Codeception\Util\Connector\Phalcon1();
        $this->client->setApplication(function () {
            $application = require \Codeception\Configuration::projectDir() . $this->config['application'];
            $di = $application->getDi();
            if (isset($this->di['db'])) {
                $di['db'] = $this->di['db'];
            }
            return $application;
        });

        $application = require \Codeception\Configuration::projectDir() . $this->config['application'];
        if (!$application instanceof \Phalcon\DI\Injectable) {
            throw new \Exception('Bootstrap must return \Phalcon\DI\Injectable object');
        }

        $this->di = $application->getDi();
        \Phalcon\DI::reset();
        \Phalcon\DI::setDefault($this->di);

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
}
