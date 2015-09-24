<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\TestCase;
use Codeception\Lib\Framework;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Connector\Symfony2 as Symfony2Connector;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\Lib\Interfaces\SupportsDomainRouting;
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
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Config
 *
 * ### Symfony 2.x
 *
 * * app_path: 'app' - specify custom path to your app dir, where bootstrap cache and kernel interface is located.
 * * environment: 'local' - environment used for load kernel
 * * debug: true - turn on/off debug mode
 * * em_service: 'doctrine.orm.entity_manager' - use the stated EntityManager to pair with Doctrine Module.
 * *
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
 * * em_service: 'doctrine.orm.entity_manager' - use the stated EntityManager to pair with Doctrine Module.
 * * debug: true - turn on/off debug mode
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
 */
class Symfony2 extends Framework implements DoctrineProvider, SupportsDomainRouting
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    public $kernel;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public $container;

    public $config = [
        'app_path' => 'app',
        'var_path' => 'app',
        'environment' => 'test',
        'debug' => true,
        'em_service' => 'doctrine.orm.entity_manager'
    ];

    /**
     * @var
     */
    protected $kernelClass;

    public $permanentServices = [];

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
        ini_set('xdebug.max_nesting_level', 200); // Symfony may have very long nesting level
    }

    public function _before(\Codeception\TestCase $test) 
    {
        $this->bootKernel();
        $this->container = $this->kernel->getContainer();
        $this->client = new Symfony2Connector($this->kernel);
        $this->client->followRedirects(true);
    }

    public function _getEntityManager()
    {
        $this->bootKernel();
        if (!$this->kernel->getContainer()->has($this->config['em_service'])) {
            return null;
        }
        $this->client->persistentServices[] = $this->config['em_service'];
        $this->client->persistentServices[] = 'doctrine.orm.default_entity_manager';
        return $this->kernel->getContainer()->get($this->config['em_service']);
    }

    protected function bootKernel()
    {
        if ($this->kernel) {
            return;
        }
        $this->kernel = new $this->kernelClass($this->config['environment'], $this->config['debug']);
        $this->kernel->boot();
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
        if (!$this->kernel->getContainer()->has('router')) {
            $this->fail('Router not found.');
        }
        $router = $this->kernel->getContainer()->get('router');
        $route = $router->getRouteCollection()->get($routeName);
        if (!$route) {
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
        if (!$this->kernel->getContainer()->has('router')) {
            $this->fail('Router not found.');
        }
        $router = $this->kernel->getContainer()->get('router');
        $route = $router->getRouteCollection()->get($routeName);
        if (!$route) {
            $this->fail(sprintf('Route with name "%s" does not exists.', $routeName));
        }

        $this->seeCurrentUrlEquals($router->generate($routeName, $params));
    }

    /**
     * Checks if any email were sent by last request
     *
     * @throws \LogicException
     */
    public function seeEmailIsSent()
    {
        $profile = $this->getProfiler();
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
     */
    public function grabServiceFromContainer($service)
    {
        if (!$this->kernel->getContainer()->has($service)) {
            $this->fail("Service $service is not available in container");
        }
        return $this->kernel->getContainer()->get($service);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Profiler\Profile
     */
    protected function getProfiler()
    {
        if (!$this->kernel->getContainer()->has('profiler')) {
            return null;
        }
        $profiler = $this->kernel->getContainer()->get('profiler');
        $response = $this->client->getResponse();
        if (null === $response) {
            $this->fail("You must perform a request before using this method.");
        }
        return $profiler->loadProfileFromResponse($response);
    }

    protected function debugResponse()
    {
        $this->debugSection('Page', $this->client->getHistory()->current()->getUri());
        if ($profile = $this->getProfiler()) {
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
}
