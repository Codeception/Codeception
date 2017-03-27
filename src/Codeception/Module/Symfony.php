<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\Framework;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Connector\Symfony as SymfonyConnector;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\Lib\Interfaces\PartedModule;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\VarDumper\Cloner\Data;

/**
 * This module uses Symfony Crawler and HttpKernel to emulate requests and test response.
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
 * * em_service: 'doctrine.orm.entity_manager' - use the stated EntityManager to pair with Doctrine Module.
 * * cache_router: 'false' - enable router caching between tests in order to [increase performance](http://lakion.com/blog/how-did-we-speed-up-sylius-behat-suite-with-blackfire)
 * * rebootable_client: 'true' - reboot client's kernel before each request
 *
 * ### Example (`functional.suite.yml`) - Symfony 2.x Directory Structure
 *
 * ```
 *    modules:
 *        - Symfony:
 *            app_path: 'app/front'
 *            environment: 'local_test'
 * ```
 *
 * ### Symfony 3.x Directory Structure
 *
 * * app_path: 'app' - specify custom path to your app dir, where the kernel interface is located.
 * * var_path: 'var' - specify custom path to your var dir, where bootstrap cache is located.
 * * environment: 'local' - environment used for load kernel
 * * em_service: 'doctrine.orm.entity_manager' - use the stated EntityManager to pair with Doctrine Module.
 * * debug: true - turn on/off debug mode
 * * cache_router: 'false' - enable router caching between tests in order to [increase performance](http://lakion.com/blog/how-did-we-speed-up-sylius-behat-suite-with-blackfire)
 * * rebootable_client: 'true' - reboot client's kernel before each request
 *
 * ### Example (`functional.suite.yml`) - Symfony 3 Directory Structure
 *
 *     modules:
 *        enabled:
 *           - Symfony:
 *               app_path: 'app/front'
 *               var_path: 'var'
 *               environment: 'local_test'
 *
 *
 * ## Public Properties
 *
 * * kernel - HttpKernel instance
 * * client - current Crawler instance
 *
 * ## Parts
 *
 * * services - allows to use Symfony DIC only with WebDriver or PhpBrowser modules.
 *
 * Usage example:
 *
 * ```yaml
 * class_name: AcceptanceTester
 * modules:
 *     enabled:
 *         - Symfony:
 *             part: SERVICES
 *         - Doctrine2:
 *             depends: Symfony
 *         - WebDriver:
 *             url: http://your-url.com
 *             browser: phantomjs
 * ```
 *
 */
class Symfony extends Framework implements DoctrineProvider, PartedModule
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    public $kernel;

    public $config = [
        'app_path' => 'app',
        'var_path' => 'app',
        'environment' => 'test',
        'debug' => true,
        'cache_router' => false,
        'em_service' => 'doctrine.orm.entity_manager',
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
     * Services that should be persistent permanently for all tests
     *
     * @var array
     */
    protected $permanentServices = [];

    /**
     * Services that should be persistent during test execution between kernel reboots
     *
     * @var array
     */
    protected $persistentServices = [];

    public function _initialize()
    {

        $this->initializeSymfonyCache();
        $this->kernelClass = $this->getKernelClass();
        $maxNestingLevel = 200; // Symfony may have very long nesting level
        $xdebugMaxLevelKey = 'xdebug.max_nesting_level';
        if (ini_get($xdebugMaxLevelKey) < $maxNestingLevel) {
            ini_set($xdebugMaxLevelKey, $maxNestingLevel);
        }

        $this->kernel = new $this->kernelClass($this->config['environment'], $this->config['debug']);
        $this->kernel->boot();

        if ($this->config['cache_router'] === true) {
            $this->persistService('router', true);
        }
    }

    /**
     * Require Symfonys bootstrap.php.cache only for PHP Version < 7
     *
     * @throws ModuleRequireException
     */
    private function initializeSymfonyCache()
    {
        $cache = Configuration::projectDir() . $this->config['var_path'] . DIRECTORY_SEPARATOR . 'bootstrap.php.cache';
        if (PHP_VERSION_ID < 70000 && !file_exists($cache)) {
            throw new ModuleRequireException(
                __CLASS__,
                "Symfony bootstrap file not found in $cache\n \n" .
                "Please specify path to bootstrap file using `var_path` config option\n \n" .
                "If you are trying to load bootstrap from a Bundle provide path like:\n \n" .
                "modules:\n    enabled:\n" .
                "    - Symfony:\n" .
                "        var_path: '../../app'\n" .
                "        app_path: '../../app'"
            );
        }
        if (file_exists($cache)) {
            require_once $cache;
        }
    }

    /**
     * Initialize new client instance before each test
     */
    public function _before(\Codeception\TestInterface $test)
    {
        $this->persistentServices = array_merge($this->persistentServices, $this->permanentServices);
        $this->client = new SymfonyConnector($this->kernel, $this->persistentServices, $this->config['rebootable_client']);
    }

    /**
     * Update permanent services after each test
     */
    public function _after(\Codeception\TestInterface $test)
    {
        foreach ($this->permanentServices as $serviceName => $service) {
            $this->permanentServices[$serviceName] = $this->grabService($serviceName);
        }
        parent::_after($test);
    }

    /**
     * Retrieve Entity Manager.
     *
     * EM service is retrieved once and then that instance returned on each call
     */
    public function _getEntityManager()
    {
        if ($this->kernel === null) {
            $this->fail('Symfony2 platform module is not loaded');
        }
        if (!isset($this->permanentServices[$this->config['em_service']])) {
            // try to persist configured EM
            $this->persistService($this->config['em_service'], true);

            if ($this->_getContainer()->has('doctrine')) {
                $this->persistService('doctrine', true);
            }
            if ($this->_getContainer()->has('doctrine.orm.default_entity_manager')) {
                $this->persistService('doctrine.orm.default_entity_manager', true);
            }
            if ($this->_getContainer()->has('doctrine.dbal.backend_connection')) {
                $this->persistService('doctrine.dbal.backend_connection', true);
            }
        }
        return $this->permanentServices[$this->config['em_service']];
    }

    /**
     * Return container.
     *
     * @return ContainerInterface
     */
    public function _getContainer()
    {
        return $this->kernel->getContainer();
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
            throw new ModuleRequireException(
                __CLASS__,
                "Can't load Kernel from $path.\n"
                . "Directory does not exists. Use `app_path` parameter to provide valid application path"
            );
        }

        $finder = new Finder();
        $finder->name('*Kernel.php')->depth('0')->in($path);
        $results = iterator_to_array($finder);
        if (!count($results)) {
            throw new ModuleRequireException(
                __CLASS__,
                "AppKernel was not found at $path. "
                . "Specify directory where Kernel class for your application is located with `app_path` parameter."
            );
        }

        $file = current($results);
        $class = $file->getBasename('.php');

        require_once $file;

        return $class;
    }

    /**
     * Get service $serviceName and add it to the lists of persistent services.
     * If $isPermanent then service becomes persistent between tests
     *
     * @param string  $serviceName
     * @param boolean $isPermanent
     */
    public function persistService($serviceName, $isPermanent = false)
    {
        $service = $this->grabService($serviceName);
        $this->persistentServices[$serviceName] = $service;
        if ($isPermanent) {
            $this->permanentServices[$serviceName] = $service;
        }
        if ($this->client) {
            $this->client->persistentServices[$serviceName] = $service;
        }
    }

    /**
     * Remove service $serviceName from the lists of persistent services.
     *
     * @param string $serviceName
     */
    public function unpersistService($serviceName)
    {
        if (isset($this->persistentServices[$serviceName])) {
            unset($this->persistentServices[$serviceName]);
        }
        if (isset($this->permanentServices[$serviceName])) {
            unset($this->permanentServices[$serviceName]);
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
        $router = $this->grabService('router');
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
        $router = $this->grabService('router');
        if (!$router->getRouteCollection()->get($routeName)) {
            $this->fail(sprintf('Route with name "%s" does not exists.', $routeName));
        }

        $uri = explode('?', $this->grabFromCurrentUrl())[0];
        try {
            $match = $router->match($uri);
        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
            $this->fail(sprintf('The "%s" url does not match with any route', $uri));
        }
        $expected = array_merge(array('_route' => $routeName), $params);
        $intersection = array_intersect_assoc($expected, $match);

        $this->assertEquals($expected, $intersection);
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
        $router = $this->grabService('router');
        if (!$router->getRouteCollection()->get($routeName)) {
            $this->fail(sprintf('Route with name "%s" does not exists.', $routeName));
        }

        $uri = explode('?', $this->grabFromCurrentUrl())[0];
        try {
            $matchedRouteName = $router->match($uri)['_route'];
        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
            $this->fail(sprintf('The "%s" url does not match with any route', $uri));
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
     * @deprecated Use grabService instead
     */
    public function grabServiceFromContainer($service)
    {
        return $this->grabService($service);
    }

    /**
     * Grabs a service from Symfony DIC container.
     * Recommended to use for unit testing.
     *
     * ``` php
     * <?php
     * $em = $I->grabService('doctrine');
     * ?>
     * ```
     *
     * @param $service
     * @return mixed
     * @part services
     */
    public function grabService($service)
    {
        $container = $this->_getContainer();
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
        $container = $this->_getContainer();
        if (!$container->has('profiler')) {
            return null;
        }

        $profiler = $this->grabService('profiler');
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
                    $roles = $profile->getCollector('security')->getRoles();

                    if ($roles instanceof Data) {
                        $roles = $this->extractRawRoles($roles);
                    }

                    $this->debugSection(
                        'User',
                        $profile->getCollector('security')->getUser()
                        . ' [' . implode(',', $roles) . ']'
                    );
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
     * @param Data $data
     * @return array
     */
    private function extractRawRoles(Data $data)
    {
        $raw = $data->getRawData();

        return isset($raw[1]) ? $raw[1] : [];
    }

    /**
     * Returns a list of recognized domain names.
     *
     * @return array
     */
    protected function getInternalDomains()
    {
        $internalDomains = [];

        $routes = $this->grabService('router')->getRouteCollection();
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

    /**
     * Reboot client's kernel.
     * Can be used to manually reboot kernel when 'rebootable_client' => false
     *
     * ``` php
     * <?php
     * ...
     * perform some requests
     * ...
     * $I->rebootClientKernel();
     * ...
     * perform other requests
     * ...
     *
     * ?>
     * ```
     *
     */
    public function rebootClientKernel()
    {
        if ($this->client) {
            $this->client->rebootKernel();
        }
    }
}
