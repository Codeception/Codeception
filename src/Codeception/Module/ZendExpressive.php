<?php
namespace Codeception\Module;

use Codeception\Lib\Framework;
use Codeception\TestInterface;
use Codeception\Configuration;
use Codeception\Lib\Connector\ZendExpressive as ZendExpressiveConnector;

/**
 * This module allows you to run tests inside Zend Expressive.
 *
 * Uses `config/container.php` file by default.
 *
 * ## Status
 *
 * * Maintainer: **Naktibalda**
 * * Stability: **alpha**
 *
 * ## Config
 *
 * * container: relative path to file which returns Container (default: `config/container.php`)
 *
 * ## API
 *
 * * application -  instance of `\Zend\Expressive\Application`
 * * container - instance of `\Interop\Container\ContainerInterface`
 * * client - BrowserKit client
 *
 */
class ZendExpressive extends Framework
{
    protected $config = [
        'container' => 'config/container.php',
    ];

    /**
     * @var \Codeception\Lib\Connector\ZendExpressive
     */
    public $client;

    /**
     * @var \Interop\Container\ContainerInterface
     */
    public $container;

    /**
     * @var \Zend\Expressive\Application
     */
    public $application;

    protected $responseCollector;

    public function _initialize()
    {
        $cwd = getcwd();
        chdir(Configuration::projectDir());
        $this->container = require Configuration::projectDir() . $this->config['container'];
        chdir($cwd);
        $this->application = $this->container->get('Zend\Expressive\Application');
        $this->initResponseCollector();
    }

    public function _before(TestInterface $test)
    {
        $this->client = new ZendExpressiveConnector();
        $this->client->setApplication($this->application);
        $this->client->setResponseCollector($this->responseCollector);
    }

    public function _after(TestInterface $test)
    {
        //Close the session, if any are open
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::_after($test);
    }

    private function initResponseCollector()
    {
        /**
         * @var Zend\Expressive\Emitter\EmitterStack
         */
        $emitterStack = $this->application->getEmitter();
        while (!$emitterStack->isEmpty()) {
            $emitterStack->pop();
        }

        $this->responseCollector = new ZendExpressiveConnector\ResponseCollector;
        $emitterStack->unshift($this->responseCollector);
    }
}
