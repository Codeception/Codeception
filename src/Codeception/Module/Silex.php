<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\TestInterface;
use Symfony\Component\HttpKernel\Client;

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
 * * em_service: 'db.orm.em' - use the stated EntityManager to pair with Doctrine Module.
 *
 * ### Bootstrap File
 *
 * Bootstrap is the same as [WebTestCase.createApplication](http://silex.sensiolabs.org/doc/testing.html#webtestcase).
 *
 * ``` php
 * <?
 * $app = require __DIR__.'/path/to/app.php';
 * $app['debug'] = true;
 * unset($app['exception_handler']);
 *
 * return $app; // optionally
 * ?>
 * ```
 *
 * ### Example (`functional.suite.yml`)
 *
 *     modules:
 *        enabled:
 *           - Silex:
 *              app: 'app/bootstrap.php'
 *
 * Class Silex
 * @package Codeception\Module
 */
class Silex extends Framework implements DoctrineProvider
{
    protected $app;
    protected $requiredFields = ['app'];
    protected $config = [
        'em_service' => 'db.orm.em'
    ];

    public function _initialize()
    {
        if (!file_exists(Configuration::projectDir() . $this->config['app'])) {
            throw new ModuleConfigException(__CLASS__, "Bootstrap file {$this->config['app']} not found");
        }

        $this->loadApp();
    }

    public function _before(TestInterface $test)
    {
        $this->loadApp();
        $this->client = new Client($this->app);
    }

    public function _getEntityManager()
    {
        if (!isset($this->app[$this->config['em_service']])) {
            return null;
        }

        return $this->app[$this->config['em_service']];
    }

    protected function loadApp()
    {
        $this->app = require Configuration::projectDir() . $this->config['app'];
        // if $app is not returned but exists
        if (isset($app)) {
            $this->app = $app;
        }
        if (!isset($this->app)) {
            throw new ModuleConfigException(__CLASS__, "\$app instance was not received from bootstrap file");
        }

        // make doctrine persistent
        $db_orm_em = $this->_getEntityManager();
        if ($db_orm_em) {
            $this->app->extend($this->config['em_service'], function () use ($db_orm_em) {
                return $db_orm_em;
            });
        }

        // some silex apps (like bolt) may rely on global $app variable
        $GLOBALS['app'] = $this->app;
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

    /**
     * Returns a list of recognized domain names
     *
     * @return array
     */
    public function getInternalDomains()
    {
        $internalDomains = [];

        foreach ($this->app['routes'] as $route) {
            if ($domain = $route->getHost()) {
                $internalDomains[] = '/^' . preg_quote($domain, '/') . '$/';
            }
        }

        return $internalDomains;
    }
}
