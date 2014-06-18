<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleRequire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * This module uses Symfony2 Crawler and HttpKernel to emulate requests and get response.
 *
 * It implements common Framework interface.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Config
 *
 * * app_path: 'app' - specify custom path to your app dir, where bootstrap cache and kernel interface is located.
 * * environment: 'local' - environment used for load kernel
 * * debug: true - switch debug mode
* 
 * ### Example (`functional.suite.yml`)
 *
 *     modules: 
 *        enabled: [Symfony2]
 *        config:
 *           Symfony2:
 *              app_path: 'app/front'
 *              environment: 'local_test'
 *              debug: true
 *
 * ## Public Properties
 *
 * * kernel - HttpKernel instance
 * * client - current Crawler instance
 * * container - dependency injection container instance
 *
 */

class Symfony2 extends \Codeception\Lib\Framework
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    public $kernel;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public $container;

    public $config = array('app_path' => 'app', 'environment' => 'test', 'debug' => true);
    
    /**
     * @var
     */
    protected $kernelClass;

    protected $clientClass = '\Symfony\Component\HttpKernel\Client';


    public function _initialize() {
        $cache = \Codeception\Configuration::projectDir() . $this->config['app_path'] . DIRECTORY_SEPARATOR . 'bootstrap.php.cache';
        if (!file_exists($cache)) throw new ModuleRequire(__CLASS__, 'Symfony2 bootstrap file not found in '.$cache);
        require_once $cache;
        $this->kernelClass = $this->getKernelClass();
        $this->kernel = new $this->kernelClass($this->config['environment'], $this->config['debug']);
    }
    
    public function _before(\Codeception\TestCase $test) {
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();
        if ($this->container->has('test.client')) { // it is Symfony2.2
            $this->client = $this->container->get('test.client');
        } else {
            $this->client = new $this->clientClass($this->kernel);
        }
        $this->client->followRedirects(true);
    }

    public function _after(\Codeception\TestCase $test) {
        $this->kernel->shutdown();
        parent::_after($test);
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
            throw new ModuleRequire(__CLASS__, 'AppKernel was not found. Specify directory where Kernel class for your application is located in "app_dir" parameter.');
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
    public function seeEmailIsSent() {
        $profile = $this->getProfiler();
        if (!$profile) \PHPUnit_Framework_Assert::fail('Emails can\'t be tested without Profiler');
        if (!$profile->hasCollector('swiftmailer')) \PHPUnit_Framework_Assert::fail('Emails can\'t be tested without SwiftMailer connector');

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
    public function grabServiceFromContainer($service) {
        if (!$this->kernel->getContainer()->has($service)) $this->fail("Service $service is not available in container");
        return $this->kernel->getContainer()->get($service);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Profiler\Profile
     */
    protected function getProfiler()
    {
        if (!$this->kernel->getContainer()->has('profiler')) return null;
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
                if ($messages) $this->debugSection('Emails', $messages . ' sent');
            }
            if ($profile->hasCollector('timer'))    $this->debugSection('Time', $profile->getCollector('timer')->getTime());
            if ($profile->hasCollector('db'))       $this->debugSection('Db', $profile->getCollector('db')->getQueryCount() . ' queries');
        }
    }
}
