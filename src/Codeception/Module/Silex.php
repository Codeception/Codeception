<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\InnerBrowser;
use Codeception\TestCase;

/**
 * Module for testing Silex applications like you would regularly do with Silex\WebTestCase.
 * This module uses Symfony2 Crawler and HttpKernel to emulate requests and get response.
 *
 * This module may be considered experimental and require feedback and pull requests from you )
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **alpha**
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
 * return $app; // optionally
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
        if (!file_exists(Configuration::projectDir() . $this->config['app'])) {
            throw new ModuleConfigException(__CLASS__, "Bootstrap file {$this->config['app']} not found");
        }
    }

    public function _before(TestCase $test)
    {
        $this->app = require Configuration::projectDir() . $this->config['app'];

        // if $app is not returned but exists
        if (isset($app)) {
            $this->app = $app;
        }

        if (!isset($this->app)) {
            throw new ModuleConfigException(__CLASS__, "\$app instance was not received from bootstrap file");
        }
        // some silex apps (like bolt) may rely on global $app variable
        $GLOBALS['app'] = $this->app;


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
     * @param  string $service
     * @return mixed
     */
    public function grabService($service)
    {
        return $this->app[$service];
    }

} 