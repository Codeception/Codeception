<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Connector\Symfony2 as Symfony2Connector;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * This module uses Symfony2 Crawler and HttpKernel to emulate requests and test response.
 *
 * ## Demo Project
 *
 * <https://github.com/DavertMik/SymfonyCodeceptionApp>
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
 *
 *
 * ### Example (`functional.suite.yml`) - Symfony 2.x Directory Structure
 *
 *     modules:
 *        enabled: [Symfony2]
 *        config:
 *           Symfony2:
 *              app_path: 'app/front'
 *              environment: 'local_test'
 *
 * ### Symfony 3.x Directory Structure
 *
 * * app_path: 'app' - specify custom path to your app dir, where the kernel interface is located.
 * * var_path: 'var' - specify custom path to your var dir, where bootstrap cache is located.
 * * environment: 'local' - environment used for load kernel
 * * debug: true - turn on/off debug mode
 *
 * ### Example (`functional.suite.yml`) - Symfony 3 Directory Structure
 *
 *     modules:
 *        enabled: [Symfony2]
 *        config:
 *           Symfony2:
 *              app_path: 'app/front'
 *              var_path: 'var'
 *              environment: 'local_test'
 *
 *
 * ## Public Properties
 *
 * * kernel - HttpKernel instance
 * * client - current Crawler instance
 * * container - dependency injection container instance
 *
 */
class Symfony2 extends \Codeception\Lib\Framework implements DoctrineProvider
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    public $kernel;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public $container;

    public $config = ['app_path' => 'app', 'var_path' => 'app', 'environment' => 'test', 'debug' => true];

    /**
     * @var
     */
    protected $kernelClass;

    public $permanentServices = [];


    public function _initialize()
    {
        $cache = \Codeception\Configuration::projectDir() . $this->config['var_path'] . DIRECTORY_SEPARATOR . 'bootstrap.php.cache';
        if (!file_exists($cache)) {
            throw new ModuleRequireException(__CLASS__, 'Symfony2 bootstrap file not found in ' . $cache);
        }
        require_once $cache;
        $this->kernelClass = $this->getKernelClass();
        $this->kernel = new $this->kernelClass($this->config['environment'], $this->config['debug']);
        ini_set('xdebug.max_nesting_level', 200); // Symfony may have very long nesting level
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();
        $this->client = new Symfony2Connector($this->kernel);
        $this->client->followRedirects(true);
    }

    public function _getEntityManager()
    {
        $this->kernel->boot();
        if (!$this->kernel->getContainer()->has('doctrine')) {
            return null;
        }
        $this->client->persistentServices[] = 'doctrine.orm.entity_manager';
        $this->client->persistentServices[] = 'doctrine.orm.default_entity_manager';
        return $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
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
        $finder = new Finder();
        $finder->name('*Kernel.php')->depth('0')->in(\Codeception\Configuration::projectDir() . $this->config['app_path']);
        $results = iterator_to_array($finder);
        if (!count($results)) {
            throw new ModuleRequireException(__CLASS__, 'AppKernel was not found. Specify directory where Kernel class for your application is located in "app_path" parameter.');
        }

        $file = current($results);
        $class = $file->getBasename('.php');

        require_once $file;

        return $class;
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
            \PHPUnit_Framework_Assert::fail('Emails can\'t be tested without Profiler');
        }
        if (!$profile->hasCollector('swiftmailer')) {
            \PHPUnit_Framework_Assert::fail('Emails can\'t be tested without SwiftMailer connector');
        }

        \PHPUnit_Framework_Assert::assertGreaterThan(0, $profile->getCollector('swiftmailer')->getMessageCount());
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
        return $profiler->loadProfileFromResponse($this->client->getResponse());
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
