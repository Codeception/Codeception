<?php
namespace Codeception\Module;

use Codeception\Lib\Framework;
use Codeception\TestCase;
use Codeception\Configuration;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Zend\Console\Console;
use Zend\EventManager\StaticEventManager;
use Zend\Mvc\Application;
use Zend\Version\Version;
use Zend\View\Helper\Placeholder\Registry;
use Codeception\Lib\Connector\ZF2 as ZF2Connector;

/**
 * This module allows you to run tests inside Zend Framework 2.
 *
 * File `init_autoloader` in project's root is required.
 * Uses `tests/application.config.php` config file by default.
 *
 * ## Status
 *
 * * Maintainer: **bladeofsteel**
 * * Stability: **alpha**
 * * Contact: https://github.com/bladeofsteel
 *
 * ## Config
 *
 * * config: relative path to config file (default: `tests/application.config.php`)
 *
 * ## API
 *
 * * application -  instance of `\Zend\Mvc\ApplicationInterface`
 * * db - instance of `\Zend\Db\Adapter\AdapterInterface`
 * * client - BrowserKit client
 *
 */
class ZF2 extends Framework implements DoctrineProvider
{
    protected $config = [
        'config' => 'tests/application.config.php',
    ];

    /**
     * @var \Zend\Mvc\ApplicationInterface
     */
    public $application;

    /**
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    public $db;

    /**
     * @var \Codeception\Lib\Connector\ZF2
     */
    public $client;

    protected $applicationConfig;

    protected $queries = 0;
    protected $time = 0;

    public function _initialize()
    {
        require Configuration::projectDir() . 'init_autoloader.php';

        $this->client = new ZF2Connector();

        $this->applicationConfig = require Configuration::projectDir() . $this->config['config'];
        if (isset($applicationConfig['module_listener_options']['config_cache_enabled'])) {
            $applicationConfig['module_listener_options']['config_cache_enabled'] = false;
        }
        Console::overrideIsConsole(false);
    }

    public function _before(TestCase $test)
    {
        $this->application = Application::init($this->applicationConfig);
        $events = $this->application->getEventManager();
        $events->detach($this->application->getServiceManager()->get('SendResponseListener'));

        $this->client->setApplication($this->application);
        $_SERVER['REQUEST_URI'] = '';
    }

    public function _after(TestCase $test)
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];

        // reset singleton
        StaticEventManager::resetInstance();

        // Reset singleton placeholder if version < 2.2.0, no longer required in 2.2.0+
        if (Version::compareVersion('2.2.0') >= 0) {
            Registry::unsetRegistry();
        }
        //Close the session, if any are open
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $this->queries = 0;
        $this->time = 0;
    }

    public function _getEntityManager()
    {
        $serviceLocator = Application::init($this->applicationConfig)->getServiceManager();
        return $serviceLocator->get('Doctrine\ORM\EntityManager');
    }
}
