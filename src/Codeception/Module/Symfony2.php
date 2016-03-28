<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\TestCase;
use Codeception\Lib\Framework;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Connector\Symfony2 as Symfony2Connector;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\Lib\Interfaces\PartedModule;
use Symfony\Component\Finder\Finder;

/**
 * This module uses Symfony2 Crawler and HttpKernel to emulate requests and test response.
 *
 * ## Demo Project
 *
 * <https://github.com/Codeception/symfony-demo>
 *
 * ## Status
 *
 * * Maintainer: **raistlin**
 * * Stability: **stable**
 *
 * ## Config
 *
 * ### Symfony 2.x
 *
 * * app_path: 'app' - specify custom path to your app dir, where bootstrap cache and kernel interface is located.
 * * environment: 'local' - environment used for load kernel
 * * debug: true - turn on/off debug mode
 * * em_service: 'doctrine.orm.default_entity_manager' - use the stated EntityManager to pair with Doctrine Module.
 * * cache_router: 'false' - enable router caching between tests in order to [increase performance](http://lakion.com/blog/how-did-we-speed-up-sylius-behat-suite-with-blackfire) 
 * * rebootable_client: 'true' - reboot client's kernel before each request
 * 
 * ### Example (`functional.suite.yml`) - Symfony 2.x Directory Structure
 *
 * ```
 *    modules:
 *        - Symfony2:
 *            app_path: 'app/front'
 *            environment: 'local_test'
 * ```
 *
 * ### Symfony 3.x Directory Structure
 *
 * * app_path: 'app' - specify custom path to your app dir, where the kernel interface is located.
 * * var_path: 'var' - specify custom path to your var dir, where bootstrap cache is located.
 * * environment: 'local' - environment used for load kernel
 * * em_service: 'doctrine.orm.default_entity_manager' - use the stated EntityManager to pair with Doctrine Module.
 * * debug: true - turn on/off debug mode
 * * cache_router: 'false' - enable router caching between tests in order to [increase performance](http://lakion.com/blog/how-did-we-speed-up-sylius-behat-suite-with-blackfire) 
 * * rebootable_client: 'true' - reboot client's kernel before each request
 *
 * ### Example (`functional.suite.yml`) - Symfony 3 Directory Structure
 *
 *     modules:
 *        enabled:
 *           - Symfony2:
 *               app_path: 'app/front'
 *               var_path: 'var'
 *               environment: 'local_test'
 *
 *
 * ## Public Properties
 *
 * * kernel - HttpKernel instance
 * * client - current Crawler instance
 * * container - dependency injection container instance
 *
 * ## Parts
 * 
 * * services - allows to use Symfony2 DIC only with WebDriver or PhpBrowser modules. 
 * 
 * Usage example:
 * 
 * ```yaml
 * class_name: AcceptanceTester
 * modules:
 *     enabled:
 *         - Symfony2:
 *             part: SERVICES
 *         - Doctrine2:
 *             depends: Symfony2
 *         - WebDriver:
 *             url: http://your-url.com
 *             browser: phantomjs
 * ```
 */
class Symfony2 extends Framework implements DoctrineProvider, PartedModule
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    public $kernel;

    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    protected $clientKernel;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public $container;

    public $config = [
        'app_path' => 'app',
        'var_path' => 'app',
        'environment' => 'test',
        'debug' => true,
        'cache_router' => false,
        'em_service' => 'doctrine.orm.default_entity_manager',
        'rebootable_client' => true,
    ];

    /**
     * @return array
     */
    public function _parts()
    {
        return ['services'];
    }

    /**
     * @var
     */
    protected $kernelClass;

    /**
     * @var array
     */
    protected $persistentServices = [];

    public function _initialize()
    {
        $cache = Configuration::projectDir() . $this->config['var_path'] . DIRECTORY_SEPARATOR . 'bootstrap.php.cache';
        if (!file_exists($cache)) {
            throw new ModuleRequireException(__CLASS__,
                "Symfony2 bootstrap file not found in $cache\n \n" .
                "Please specify path to bootstrap file using `var_path` config option\n \n" .
                "If you are trying to load bootstrap from a Bundle provide path like:\n \n" .
                "modules:\n    enabled:\n" .
                "    - Symfony2:\n" .
                "        var_path: '../../app'\n" .
                "        app_path: '../../app'");

        }
        require_once $cache;
        $this->kernelClass = $this->getKernelClass();
        $maxNestingLevel = 200; // Symfony may have very long nesting level
        $xdebugMaxLevelKey = 'xdebug.max_nesting_level';
        if (ini_get($xdebugMaxLevelKey) < $maxNestingLevel) {
            ini_set($xdebugMaxLevelKey, $maxNestingLevel);
        }

        $this->kernel = new $this->kernelClass($this->config['environment'], $this->config['debug']);
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();

        if ($this->config['cache_router'] === true) {
            $this->persistService('router');
        }

        $this->clientKernel = clone $this->kernel;
        $this->clientKernel->boot();
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->client = new Symfony2Connector($this->clientKernel, $this->persistentServices, $this->config['rebootable_client']);
    }

    /**
     * Retrieve Entity Manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function _getEntityManager()
    {
        if ($this->kernel === null) {
            $this->fail('Symfony2 platform module is not loaded');
        }
        if (isset($this->persistentServices[$this->config['em_service']])) {
            $em = $this->persistentServices[$this->config['em_service']];
            if ($em instanceof \Doctrine\ORM\EntityManager) {
                return $em;
            }
        }
        $em = $this->grabServiceFromContainer($this->config['em_service']);
        if ($em instanceof \Doctrine\ORM\EntityManager) {
            $this->persistService($this->config['em_service']);
        }
        return $em;
    }

    /**
     * Attempts to guess the kernel location.
     *
     * When the Kernel is located, the file is required.
     *
     * @return string The Kernel class name
     */
    protected function getKernelClass()
    {
        $path = \Codeception\Configuration::projectDir() . $this->config['app_path'];
        if (!file_exists(\Codeception\Configuration::projectDir() . $this->config['app_path'])) {
            throw new ModuleRequireException(__CLASS__, "Can't load Kernel from $path.\nDirectory does not exists. Use `app_path` parameter to provide valid application path");
        }

        $finder = new Finder();
        $finder->name('*Kernel.php')->depth('0')->in($path);
        $results = iterator_to_array($finder);
        if (!count($results)) {
            throw new ModuleRequireException(__CLASS__, "AppKernel was not found at $path. Specify directory where Kernel class for your application is located with `app_path` parameter.");
        }

        $file = current($results);
        $class = $file->getBasename('.php');

        require_once $file;

        return $class;
    }

    /**
     * Get service $serviceName and add it to the list of persistent services.
     * 
     * @param string $serviceName
     */
    public function persistService($serviceName)
    {
        $this->persistentServices[$serviceName] = $this->grabServiceFromContainer($serviceName);
        if ($this->client) {
            $this->client->persistentServices[$serviceName] = $this->persistentServices[$serviceName];
        }
    }
            
    /**
     * Remove service $serviceName from the list of persistent services.
     * 
     * @param string $serviceName
     */
    public function unpersistService($serviceName)
    {
        if (isset($this->persistentServices[$serviceName])) {
            unset($this->persistentServices[$serviceName]);
        }
        if ($this->client && isset($this->client->persistentServices[$serviceName])) {
            unset($this->client->persistentServices[$serviceName]);
        }
    }
            
    /**
     * Invalidate previously cached routes.
     */
    public function invalidateCachedRouter()
    {
        $this->unpersistService('router');
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
        $router = $this->grabServiceFromContainer('router');
        if (!$router->getRouteCollection()->get($routeName)) {
            $this->fail(sprintf('Route with name "%s" does not exists.', $routeName));
        }
        $url = $router->generate($routeName, $params);
        $this->amOnPage($url);
    }

    /**
     * Checks that current url matches route.
     *
     * ``` php
     * <?php
     * $I->seeCurrentRouteIs('posts.index');
     * $I->seeCurrentRouteIs('posts.show', array('id' => 8));
     * ?>
     * ```
     *
     * @param $routeName
     * @param array $params
     */
    public function seeCurrentRouteIs($routeName, array $params = [])
    {
        $router = $this->grabServiceFromContainer('router');
        if (!$router->getRouteCollection()->get($routeName)) {
            $this->fail(sprintf('Route with name "%s" does not exists.', $routeName));
        }
        $url = $router->generate($routeName, $params);
        $this->seeCurrentUrlEquals($url);
    }

    /**
     * Checks that current url matches route.
     * Unlike seeCurrentRouteIs, this can matches without exact route parameters
     *
     * ``` php
     * <?php
     * $I->seeCurrentRouteMatches('my_blog_pages');
     * ?>
     * ```
     *
     * @param $routeName
     */
    public function seeInCurrentRoute($routeName)
    {
        $router = $this->grabServiceFromContainer('router');
        if (!$router->getRouteCollection()->get($routeName)) {
            $this->fail(sprintf('Route with name "%s" does not exists.', $routeName));
        }

        try {
            $matchedRouteName = $router->match($this->grabFromCurrentUrl())['_route'];
        } catch (\Exception\ResourceNotFoundException $e) {
            $this->fail(sprintf('The "%s" url does not match with any route', $routeName));
        }
        
        $this->assertEquals($matchedRouteName, $routeName);
    }

    /**
     * Checks if any email were sent by last request
     *
     * @throws \LogicException
     */
    public function seeEmailIsSent()
    {
        $profile = $this->getProfile();
        if (!$profile) {
            $this->fail('Emails can\'t be tested without Profiler');
        }
        if (!$profile->hasCollector('swiftmailer')) {
            $this->fail('Emails can\'t be tested without SwiftMailer connector');
        }

        $this->assertGreaterThan(0, $profile->getCollector('swiftmailer')->getMessageCount());
    }

    /**
     * Grabs a service from Symfony DIC container.
     * Recommended to use for unit testing.
     *
     * ``` php
     * <?php
     * $em = $I->grabServiceFromContainer('doctrine');
     * ?>
     * ```
     *
     * @param $service
     * @return mixed
     * @part services
     */
    public function grabServiceFromContainer($service)
    {
        $container = $this->client ? $this->client->getContainer() : $this->kernel->getContainer();
        if (!$container->has($service)) {
            $this->fail("Service $service is not available in container");
        }
        return $container->get($service);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Profiler\Profile
     */
    protected function getProfile()
    {
        $profiler = $this->grabServiceFromContainer('profiler');
        $response = $this->client->getResponse();
        if (null === $response) {
            $this->fail("You must perform a request before using this method.");
        }
        return $profiler->loadProfileFromResponse($response);
    }

    /**
     * @param $url
     */
    protected function debugResponse($url)
    {
        parent::debugResponse($url);

        if ($profile = $this->getProfile()) {
            if ($profile->hasCollector('security')) {
                if ($profile->getCollector('security')->isAuthenticated()) {
                    $this->debugSection('User', $profile->getCollector('security')->getUser() . ' [' . implode(',', $profile->getCollector('security')->getRoles()) . ']');
                } else {
                    $this->debugSection('User', 'Anonymous');
                }
            }
            if ($profile->hasCollector('swiftmailer')) {
                $messages = $profile->getCollector('swiftmailer')->getMessageCount();
                if ($messages) {
                    $this->debugSection('Emails', $messages . ' sent');
                }
            }
            if ($profile->hasCollector('timer')) {
                $this->debugSection('Time', $profile->getCollector('timer')->getTime());
            }
        }
    }

    /**
     * Returns a list of recognized domain names.
     *
     * @return array
     */
    protected function getInternalDomains()
    {
        $internalDomains = [];

        $routes = $this->grabServiceFromContainer('router')->getRouteCollection();
        /* @var \Symfony\Component\Routing\Route $route */
        foreach ($routes as $route) {
            if (!is_null($route->getHost())) {
                $compiled = $route->compile();
                if (!is_null($compiled->getHostRegex())) {
                    $internalDomains[] = $compiled->getHostRegex();
                }
            }
        }

        return array_unique($internalDomains);
    }
}
