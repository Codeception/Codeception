<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfig;
use Codeception\TestCase;
use Codeception\Util\InnerBrowser;

/**
 * Module for testing Silex applications like you would regularly do with Silex\WebTestCase.
 * This module uses Symfony2 Crawler and HttpKernel to emulate requests and get response.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: davert.codecept@resend.cc
 *
 * ## Config
 *
 * * app: **required** - path to Silex bootstrap file.
 *
 * ### Bootstrap File
 *
 * Bootstrap is the same as [WebTestCase.createApplication](http://silex.sensiolabs.org/doc/testing.html#webtestcase) should be.
 *
 * ``` php
 * <?
 * $app = require __DIR__.'/path/to/app.php';
 * $app['debug'] = true;
 * $app['exception_handler']->disable();
 *
 * return $app;
 * ?>
 * ```
 *
 * ### Example (`functional.suite.yml`)
 *
 *     modules:
 *        enabled: [Silex]
 *        config:
 *           Silex:
 *              app: 'app/bootstrap.php'
 *
 * Class Silex
 * @package Codeception\Module
 */
class Silex extends InnerBrowser
{

    protected $app;
    protected $requiredFields = ['app'];

    public function _initialize()
    {
        if (!file_exists($this->config['app'])) {
            throw new ModuleConfig(__CLASS__, "Bootstrap file {$this->config['app']} not found");
        }
    }

    public function _before(TestCase $test)
    {
        $this->app = require $this->config['app'];
        $this->client = new \Symfony\Component\HttpKernel\Client($this->app);
    }

    /**
     * Return an instance of a class from the Container.
     *
     * Example
     * ``` php
     * <?php
     * $I->grabService('session');
     * ?>
     * ```
     *
     * @param  string $class
     * @return mixed
     */
    public function grabService($class)
    {
        return $this->app[$class];
    }

} 