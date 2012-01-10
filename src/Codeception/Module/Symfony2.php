<?php
namespace Codeception\Module;

/**
 * This module uses Symfony2 Crawler and HttpKernel to emulate requests and get response.
 *
 * It implements common Framework interface.
 *
 * ## Config
 *
 * * app_path ('app' by default) - specify custom path to your app dir, where bootstrap cache and kernel interface is located.
 *
 *  ## Public Properties
 *
 * * kernel - HttpKernel instance
 * * client - current Crawler instance
 *
 */

use Codeception\Util\Connector\Symfony2 as Connector;

class Symfony2 extends \Codeception\Util\Framework
{
    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    public $kernel;

    public $fields = array('app_path' => 'app');
    /**
     * @var
     */
    protected $kernelClass;


    public function _initialize() {
        $cache = getcwd().DIRECTORY_SEPARATOR.$this->config['app_path'].DIRECTORY_SEPARATOR.'bootstrap.php.cache';
        if (!file_exists($cache)) throw new \RuntimeException('Symfony2 bootstrap file not found in '.$cache);
        require_once $cache;
        $this->kernelClass = $this->getKernelClass();
    }
    
    public function _before(\Codeception\TestCase $test) {
        $this->kernel = new $this->kernelClass('test', true);
        $this->kernel->loadClassCache();

        $this->client = new Connector($this->kernel);
    }

    public function _after(\Codeception\TestCase $test) {
        $this->kernel->shutdown();
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
        $finder->name('*Kernel.php')->depth('0')->in($this->config['app_path']);
        $results = iterator_to_array($finder);
        if (!count($results)) {
            throw new \RuntimeException('Provide kernel_dir as parameter for Symfony2 module');
        }

        $file = current($results);
        $class = $file->getBasename('.php');

        require_once $file;

        return $class;
    }

    protected function getBootstrapCache()
    {

    }

}
