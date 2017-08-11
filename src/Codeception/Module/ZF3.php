<?php

namespace Test\Helper;

use Codeception\Configuration;
use Codeception\Lib\Connector\ZF3 as ZF3Connector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\TestInterface;
use Codeception\Util\ReflectionHelper;
use Doctrine\ORM\EntityManager;
use Exception;
use RuntimeException;
use Traversable;
use Zend\Console\Console;
use Zend\Mvc\ApplicationInterface;
use Zend\Router\Http\Hostname;
use Zend\Router\Http\Part;
use Zend\Router\RouteInterface;

/**
 * @author Andy O'Brien <oban0601@gmail.com>
 */
class ZF3 extends Framework implements DoctrineProvider, PartedModule
{
    const APPLICATION_CONFIG = 'config/application.config.php';

    /** @var ZF3Connector */
    public $client;

    /** @var ApplicationInterface */
    protected $application;

    /** @var array */
    private $applicationConfig;

    /** @var array */
    private $domainCollector = [];

    /**
     * @return void
     */
    public function _initialize(): void
    {
        $config = Configuration::projectDir() . self::APPLICATION_CONFIG;

        if (file_exists($config)) {
            $this->applicationConfig = require $config;
        } else {
            throw new RuntimeException(sprintf('Application config file "%s" does not exist', $config));
        }

        if (isset($this->applicationConfig['module_listener_options']['config_cache_enabled'])) {
            $this->applicationConfig['module_listener_options']['config_cache_enabled'] = false;
        }

        Console::overrideIsConsole(false);

        $this->client = new ZF3Connector([], null, null, $this->applicationConfig);
        $this->client->createApplication();
    }

    /**
     * @param TestInterface $test
     *
     * @return void
     */
    public function _before(TestInterface $test): void
    {
        $_SERVER['REQUEST_URI'] = '';
    }

    /**
     * @param TestInterface $test
     *
     * @return void
     */
    public function _after(TestInterface $test): void
    {
        $_SESSION = [];
        $_GET     = [];
        $_POST    = [];
        $_COOKIE  = [];

        //Close the session, if any are open
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::_after($test);
    }

    /**
     * @return void
     */
    public function _afterSuite(): void
    {
        unset($this->client);
    }

    /**
     * Grabs a service from ZF3 container.
     * Recommended to use for unit testing.
     * ``` php
     * <?php
     * $em = $I->grabServiceFromContainer('Doctrine\ORM\EntityManager');
     * ?>
     * ```
     *
     * @param $service
     *
     * @return mixed
     * @part services
     */
    public function grabServiceFromContainer($service)
    {
        return $this->client->grabServiceFromContainer($service);
    }

    /**
     * Adds service to ZF3 container
     *
     * @param string $name
     * @param object $service
     *
     * @part services
     *
     * @return void
     */
    public function addServiceToContainer($name, $service): void
    {
        $this->client->addServiceToContainer($name, $service);
    }

    /**
     * @return EntityManager
     */
    public function _getEntityManager(): EntityManager
    {
        return $this->grabServiceFromContainer(EntityManager::class);
    }

    /**
     * Opens web page using route name and parameters.
     * ``` php
     * <?php
     * $I->amOnRoute('posts.create');
     * $I->amOnRoute('posts.show', array('id' => 34));
     * ?>
     * ```
     *
     * @param       $routeName
     * @param array $params
     *
     * @return void
     */
    public function amOnRoute($routeName, array $params = []): void
    {
        /** @var RouteInterface $router */
        $router = $this->client->grabServiceFromContainer('router');
        $url    = $router->assemble($params, ['name' => $routeName]);

        $this->amOnPage($url);
    }

    /**
     * Checks that current url matches route.
     * ``` php
     * <?php
     * $I->seeCurrentRouteIs('posts.index');
     * $I->seeCurrentRouteIs('posts.show', ['id' => 8]));
     * ?>
     * ```
     *
     * @param       $routeName
     * @param array $params
     *
     * @return void
     */
    public function seeCurrentRouteIs($routeName, array $params = []): void
    {
        $router = $this->client->grabServiceFromContainer('router');
        $url    = $router->assemble($params, ['name' => $routeName]);

        $this->seeCurrentUrlEquals($url);
    }

    /**
     * @return array
     */
    public function _parts(): array
    {
        return [
            'services',
        ];
    }

    /**
     * @return array
     */
    protected function getInternalDomains(): array
    {
        $router                = $this->client->grabServiceFromContainer('router');
        $this->domainCollector = [];

        $this->addInternalDomainsFromRoutes($router->getRoutes());

        return array_unique($this->domainCollector);
    }

    /**
     * @param Traversable $routes
     *
     * @return void
     */
    private function addInternalDomainsFromRoutes($routes): void
    {
        foreach ($routes as $name => $route) {
            if ($route instanceof Hostname) {
                $this->addInternalDomain($route);
            } elseif ($route instanceof Part) {
                $parentRoute = ReflectionHelper::readPrivateProperty($route, 'route');
                if ($parentRoute instanceof Hostname) {
                    $this->addInternalDomain($parentRoute);
                }
                // this is necessary to instantiate child routes
                try {
                    $route->assemble();
                } catch (Exception $e) {
                }
                $this->addInternalDomainsFromRoutes($route->getRoutes());
            }
        }
    }

    /**
     * @param $route
     *
     * @return void
     */
    private function addInternalDomain($route): void
    {
        $this->domainCollector [] = '/^' . ReflectionHelper::readPrivateProperty($route, 'regex') . '$/';
    }
}
