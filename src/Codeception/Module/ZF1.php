<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\Framework;
use Codeception\TestInterface;
use Codeception\Exception\ModuleException;
use Codeception\Util\ReflectionHelper;
use Codeception\Lib\Connector\ZF1 as ZF1Connector;
use Zend_Controller_Router_Route_Hostname;
use Zend_Controller_Router_Route_Chain;

/**
 * This module allows you to run tests inside Zend Framework.
 * It acts just like ControllerTestCase, but with usage of Codeception syntax.
 *
 * It assumes, you have standard structure with __APPLICATION_PATH__ set to './application'
 * and LIBRARY_PATH set to './library'. If it's not then set the appropriate path in the Config.
 *
 * [Tutorial](http://codeception.com/01-27-2012/bdd-with-zend-framework.html)
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Config
 *
 * * env  - environment used for testing ('testing' by default).
 * * config - relative path to your application config ('application/configs/application.ini' by default).
 * * app_path - relative path to your application folder ('application' by default).
 * * lib_path - relative path to your library folder ('library' by default).
 *
 * ## API
 *
 * * client - BrowserKit client
 * * db - current instance of Zend_Db_Adapter
 * * bootstrap - current bootstrap file.
 *
 * ## Cleaning up
 *
 * Unfortunately Zend_Db doesn't support nested transactions,
 * thus, for cleaning your database you should either use standard Db module or
 * [implement nested transactions yourself](http://blog.ekini.net/2010/03/05/zend-framework-how-to-use-nested-transactions-with-zend_db-and-mysql/).
 *
 * If your database supports nested transactions (MySQL doesn't)
 * or you implemented them you can put all your code inside a transaction.
 * Use a generated helper TestHelper. Use this code inside of it.
 *
 * ``` php
 * <?php
 * namespace Codeception\Module;
 * class TestHelper extends \Codeception\Module {
 *      function _before($test) {
 *          $this->getModule('ZF1')->db->beginTransaction();
 *      }
 *
 *      function _after($test) {
 *          $this->getModule('ZF1')->db->rollback();
 *      }
 * }
 * ?>
 * ```
 *
 * This will make your functional tests run super-fast.
 *
 */
class ZF1 extends Framework
{
    protected $config = [
        'env' => 'testing',
        'config' => 'application/configs/application.ini',
        'app_path' => 'application',
        'lib_path' => 'library'
    ];

    /**
     * @var \Zend_Application
     */
    public $bootstrap;

    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    public $db;

    /**
     * @var \Codeception\Lib\Connector\ZF1
     */
    public $client;

    protected $queries = 0;
    protected $time = 0;


    /**
     * @var array Used to collect domains while recursively traversing route tree
     */
    private $domainCollector = [];

    public function _initialize()
    {
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', $this->config['env']);
        defined('APPLICATION_PATH') || define(
            'APPLICATION_PATH',
            Configuration::projectDir() . $this->config['app_path']
        );
        defined('LIBRARY_PATH') || define('LIBRARY_PATH', Configuration::projectDir() . $this->config['lib_path']);

        // Ensure library/ is on include_path
        set_include_path(
            implode(
                PATH_SEPARATOR,
                [
                    LIBRARY_PATH,
                    get_include_path(),
                ]
            )
        );

        require_once 'Zend/Loader/Autoloader.php';
        \Zend_Loader_Autoloader::getInstance();
    }

    public function _before(TestInterface $test)
    {
        $this->client = new ZF1Connector();

        \Zend_Session::$_unitTestEnabled = true;
        try {
            $this->bootstrap = new \Zend_Application(
                $this->config['env'],
                Configuration::projectDir() . $this->config['config']
            );
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
        $this->bootstrap->bootstrap();
        $this->client->setBootstrap($this->bootstrap);

        $db = $this->bootstrap->getBootstrap()->getResource('db');
        if ($db instanceof \Zend_Db_Adapter_Abstract) {
            $this->db = $db;
            $this->db->getProfiler()->setEnabled(true);
            $this->db->getProfiler()->clear();
        }
    }

    public function _after(TestInterface $test)
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        if ($this->bootstrap) {
            $fc = $this->bootstrap->getBootstrap()->getResource('frontcontroller');
            if ($fc) {
                $fc->resetInstance();
            }
        }
        \Zend_Layout::resetMvcInstance();
        \Zend_Controller_Action_HelperBroker::resetHelpers();
        \Zend_Session::$_unitTestEnabled = true;
        \Zend_Registry::_unsetInstance();
        $this->queries = 0;
        $this->time = 0;

        parent::_after($test);
    }

    /**
     * @param $url
     */
    protected function debugResponse($url)
    {
        parent::debugResponse($url);

        $this->debugSection('Session', json_encode($_COOKIE));
        if ($this->db) {
            $profiler = $this->db->getProfiler();
            $queries = $profiler->getTotalNumQueries() - $this->queries;
            $time = $profiler->getTotalElapsedSecs() - $this->time;
            $this->debugSection('Db', $queries . ' queries');
            $this->debugSection('Time', round($time, 2) . ' secs taken');
            $this->time = $profiler->getTotalElapsedSecs();
            $this->queries = $profiler->getTotalNumQueries();
        }
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
        $router = $this->bootstrap->getBootstrap()->getResource('frontcontroller')->getRouter();
        $url = $router->assemble($params, $routeName);
        $this->amOnPage($url);
    }

    /**
     * Checks that current url matches route.
     *
     * ``` php
     * <?php
     * $I->seeCurrentRouteIs('posts.index');
     * $I->seeCurrentRouteIs('posts.show', ['id' => 8]));
     * ?>
     * ```
     *
     * @param $routeName
     * @param array $params
     */
    public function seeCurrentRouteIs($routeName, array $params = [])
    {
        $router = $this->bootstrap->getBootstrap()->getResource('frontcontroller')->getRouter();
        $url = $router->assemble($params, $routeName);
        $this->seeCurrentUrlEquals($url);
    }

    protected function getInternalDomains()
    {
        $router = $this->bootstrap->getBootstrap()->getResource('frontcontroller')->getRouter();
        $this->domainCollector = [];
        $this->addInternalDomainsFromRoutes($router->getRoutes());
        return array_unique($this->domainCollector);
    }

    private function addInternalDomainsFromRoutes($routes)
    {
        foreach ($routes as $name => $route) {
            try {
                $route->assemble([]);
            } catch (\Exception $e) {
            }
            if ($route instanceof Zend_Controller_Router_Route_Hostname) {
                $this->addInternalDomain($route);
            } elseif ($route instanceof Zend_Controller_Router_Route_Chain) {
                $chainRoutes = ReflectionHelper::readPrivateProperty($route, '_routes');
                $this->addInternalDomainsFromRoutes($chainRoutes);
            }
        }
    }

    private function addInternalDomain(Zend_Controller_Router_Route_Hostname $route)
    {
        $parts = ReflectionHelper::readPrivateProperty($route, '_parts');
        foreach ($parts as &$part) {
            if ($part === null) {
                $part = '[^.]+';
            }
        }
        $regex = implode('\.', $parts);
        $this->domainCollector []= '/^' . $regex . '$/iu';
    }
}
