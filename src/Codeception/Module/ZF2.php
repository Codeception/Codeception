<?php
namespace Codeception\Module;

use Codeception\Codecept;
use Zend\Console\Console;
use Zend\EventManager\StaticEventManager;
use Zend\Mvc\Application;
use Zend\View\Helper\Placeholder;
use Zend\Version\Version;

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

class ZF2 extends \Codeception\Lib\Framework
{
    protected $config = array(
        'config' => 'tests/application.config.php',
    );

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

    protected $queries = 0;
    protected $time = 0;

    public function _initialize() {
        require \Codeception\Configuration::projectDir().'init_autoloader.php';

        $this->client = new \Codeception\Lib\Connector\ZF2();
    }

    public function _before(\Codeception\TestCase $test) {
        $applicationConfig = require \Codeception\Configuration::projectDir() . $this->config['config'];
        if (isset($applicationConfig['module_listener_options']['config_cache_enabled'])) {
            $applicationConfig['module_listener_options']['config_cache_enabled'] = false;
        }
        Console::overrideIsConsole(false);
        $this->application = Application::init($applicationConfig);
        $events = $this->application->getEventManager();
        $events->detach($this->application->getServiceManager()->get('SendResponseListener'));

        $this->client->setApplication($this->application);
        $_SERVER['REQUEST_URI'] = '';
    }

    public function _after(\Codeception\TestCase $test) {
        $_SESSION = array();
        $_GET     = array();
        $_POST    = array();
        $_COOKIE  = array();

        // reset singleton
        StaticEventManager::resetInstance();
        
        // Reset singleton placeholder if version < 2.2.0, no longer required in 2.2.0+
        if (Version::compareVersion('2.2.0') >= 0) {
            Placeholder\Registry::unsetRegistry();
        }
        //Close the session, if any are open
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $this->queries = 0;
        $this->time = 0;
    }
}
