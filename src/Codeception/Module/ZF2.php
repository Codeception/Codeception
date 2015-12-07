<?php
namespace Codeception\Module;

use Codeception\Lib\Framework;
use Codeception\TestCase;
use Codeception\Configuration;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\Util\ReflectionHelper;
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

    /**
     * @var array Used to collect domains while recusively traversing route tree
     */
    private $domainCollector = [];

    public function _initialize()
    {
        require Configuration::projectDir() . 'init_autoloader.php';

        $this->applicationConfig = require Configuration::projectDir() . $this->config['config'];
        if (isset($applicationConfig['module_listener_options']['config_cache_enabled'])) {
            $applicationConfig['module_listener_options']['config_cache_enabled'] = false;
        }
        Console::overrideIsConsole(false);
    }

    public function _before(TestCase $test)
    {
        $this->client = new ZF2Connector();

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

        parent::_after($test);
    }

    public function _getEntityManager()
    {
        return $this->grabServiceFromContainer('Doctrine\ORM\EntityManager');
    }

    /**
     * Grabs a service from ZF2 container.
     * Recommended to use for unit testing.
     *
     * ``` php
     * <?php
     * $em = $I->grabServiceFromContainer('Doctrine\ORM\EntityManager');
     * ?>
     * ```
     *
     * @param $service
     * @return mixed
     */
    public function grabServiceFromContainer($service)
    {
        $serviceLocator = $this->application
            ? $this->application->getServiceManager()
            : Application::init($this->applicationConfig)->getServiceManager();
        if (!$serviceLocator->has($service)) {
            $this->fail("Service $service is not available in container");
        }
        return $serviceLocator->get($service);
    }

    /**
     * Opens web page using route name and parameters.
     *
     * ``` php
     * <?php
     * $I->amOnRoute('posts.create');
     * $I->amOnRoute('posts.show', array('id' => 34));
     * ?>
     * ```
     *
     * @param $routeName
     * @param array $params
     */
    public function amOnRoute($routeName, array $params = [])
    {
        $router = $this->application->getServiceManager()->get('router');
        $url = $router->assemble($params, ['name' => $routeName]);
        $this->amOnPage($url);
    }

    /**
     * Checks that current url matches route.
     *
     * ``` php
     * <?php
     * $I->seeCurrentRouteIs('posts.index');
     * $I->seeCurrentRouteIs('posts.show', ['id' => 8]));
     * ?>
     * ```
     *
     * @param $routeName
     * @param array $params
     */
    public function seeCurrentRouteIs($routeName, array $params = [])
    {
        $router = $this->application->getServiceManager()->get('router');
        $url = $router->assemble($params, ['name' => $routeName]);
        $this->seeCurrentUrlEquals($url);
    }

    protected function getInternalDomains()
    {
        /**
         * @var Zend\Mvc\Router\Http\TreeRouteStack
         */
        $router = $this->application->getServiceManager()->get('router');
        $this->domainCollector = [];
        $this->addInternalDomainsFromRoutes($router->getRoutes());
        return array_unique($this->domainCollector);
    }

    private function addInternalDomainsFromRoutes($routes)
    {
        foreach ($routes as $name => $route) {
            if ($route instanceof \Zend\Mvc\Router\Http\Hostname) {
                $this->addInternalDomain($route);
            } elseif ($route instanceof \Zend\Mvc\Router\Http\Part) {
                $parentRoute = ReflectionHelper::readPrivateProperty($route, 'route');
                if ($parentRoute instanceof \Zend\Mvc\Router\Http\Hostname) {
                    $this->addInternalDomain($parentRoute);
                }
                // this is necessary to instantiate child routes
                try {
                    $route->assemble([], []);
                } catch (\Exception $e) {
                }
                $this->addInternalDomainsFromRoutes($route->getRoutes());
            }
        }
    }

    private function addInternalDomain(\Zend\Mvc\Router\Http\Hostname $route)
    {
        $regex = ReflectionHelper::readPrivateProperty($route, 'regex');
        $this->domainCollector []= '/^' . $regex . '$/';
    }
}
