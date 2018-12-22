<?php
namespace Codeception\Module;

use Codeception\Lib\Framework;
use Codeception\TestInterface;
use Codeception\Configuration;
use Codeception\Lib\Connector\ZendExpressive as ZendExpressiveConnector;
use Codeception\Lib\Interfaces\DoctrineProvider;

/**
 * This module allows you to run tests inside Zend Expressive.
 *
 * Uses `config/container.php` file by default.
 *
 * ## Status
 *
 * * Maintainer: **Naktibalda**
 * * Stability: **alpha**
 *
 * ## Config
 *
 * * container: relative path to file which returns Container (default: `config/container.php`)
 *
 * ## API
 *
 * * application -  instance of `\Zend\Expressive\Application`
 * * container - instance of `\Interop\Container\ContainerInterface`
 * * client - BrowserKit client
 *
 */
class ZendExpressive extends Framework implements DoctrineProvider
{
    protected $config = [
        'container' => 'config/container.php',
    ];

    /**
     * @var \Codeception\Lib\Connector\ZendExpressive
     */
    public $client;

    /**
     * @var \Interop\Container\ContainerInterface
     */
    public $container;

    /**
     * @var \Zend\Expressive\Application
     */
    public $application;

    protected $responseCollector;

    public function _initialize()
    {
        $cwd = getcwd();
        $projectDir = Configuration::projectDir();
        chdir($projectDir);
        $this->container = require $projectDir . $this->config['container'];
        $app = $this->container->get(\Zend\Expressive\Application::class);

        $middlewareFactory = null;
        if ($this->container->has(\Zend\Expressive\MiddlewareFactory::class)) {
            $middlewareFactory = $this->container->get(\Zend\Expressive\MiddlewareFactory::class);
        }

        $pipelineFile = $projectDir . 'config/pipeline.php';
        if (file_exists($pipelineFile)) {
            $pipelineFunction = require $pipelineFile;
            if (is_callable($pipelineFunction) && $middlewareFactory) {
                $pipelineFunction($app, $middlewareFactory, $this->container);
            }
        }
        $routesFile = $projectDir . 'config/routes.php';
        if (file_exists($routesFile)) {
            $routesFunction = require $routesFile;
            if (is_callable($routesFunction) && $middlewareFactory) {
                $routesFunction($app, $middlewareFactory, $this->container);
            }
        }
        chdir($cwd);

        $this->application = $app;
        $this->initResponseCollector();
    }

    public function _before(TestInterface $test)
    {
        $this->client = new ZendExpressiveConnector();
        $this->client->setApplication($this->application);
        if ($this->responseCollector) {
            $this->client->setResponseCollector($this->responseCollector);
        }
    }

    public function _after(TestInterface $test)
    {
        //Close the session, if any are open
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::_after($test);
    }

    private function initResponseCollector()
    {
        if (!method_exists($this->application, 'getEmitter')) {
            //Does not exist in Zend Expressive v3
            return;
        }

        /**
         * @var Zend\Expressive\Emitter\EmitterStack
         */
        $emitterStack = $this->application->getEmitter();
        while (!$emitterStack->isEmpty()) {
            $emitterStack->pop();
        }

        $this->responseCollector = new ZendExpressiveConnector\ResponseCollector;
        $emitterStack->unshift($this->responseCollector);
    }

    public function _getEntityManager()
    {
        $service = 'Doctrine\ORM\EntityManager';
        if (!$this->container->has($service)) {
            throw new \PHPUnit\Framework\AssertionFailedError("Service $service is not available in container");
        }

        return $this->container->get('Doctrine\ORM\EntityManager');
    }
}
