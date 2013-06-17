<?php
namespace Codeception\Module;

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
 * For Doctrine1 and Doctrine2 all queries are put inside rollback transaction. If you are using one of this ORMs connect their modules to speed up testing.
 *
 * Unfortunately Zend_Db doesn't support nested transactions, thus, for cleaning your database you should either use standard Db module or
 * [implement nested transactions yourself](http://blog.ekini.net/2010/03/05/zend-framework-how-to-use-nested-transactions-with-zend_db-and-mysql/).
 *
 * If your database supports nested transactions (MySQL doesn't) or you implemented them you can put all your code inside a transaction.
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

class ZF1 extends \Codeception\Util\Framework implements \Codeception\Util\FrameworkInterface
{
    protected $config = array('env' => 'testing', 'config' => 'application/configs/application.ini',
        'app_path' => 'application', 'lib_path' => 'library');

    /**
     * @var \Zend_Application
     */
    public $bootstrap;

    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    public $db;

    /**
     * @var \Codeception\Util\Connector\ZF1
     */
    public $client;

    protected $queries = 0;
    protected $time = 0;

    public function _initialize() {
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', $this->config['env']);
        defined('APPLICATION_PATH') || define('APPLICATION_PATH', getcwd().DIRECTORY_SEPARATOR.$this->config['app_path']);
        defined('LIBRARY_PATH') || define('LIBRARY_PATH', getcwd().DIRECTORY_SEPARATOR.$this->config['lib_path']);

        // Ensure library/ is on include_path
        set_include_path(implode(PATH_SEPARATOR, array(
            LIBRARY_PATH,
            get_include_path(),
        )));

        require_once 'Zend/Loader/Autoloader.php';
        \Zend_Loader_Autoloader::getInstance();
        $this->client = new \Codeception\Util\Connector\ZF1();
    }

    public function _before(\Codeception\TestCase $test) {
        \Zend_Session::$_unitTestEnabled = true;
        $this->bootstrap = new \Zend_Application($this->config['env'], getcwd().DIRECTORY_SEPARATOR.$this->config['config']);
        $this->bootstrap->bootstrap();
        $this->client->setBootstrap($this->bootstrap);

        $db = $this->bootstrap->getBootstrap()->getResource('db');
        if ($db instanceof \Zend_Db_Adapter_Abstract) {
            $this->db = $db;
            $this->db->getProfiler()->setEnabled(true);
            $this->db->getProfiler()->clear();
        }
    }

    public function _after(\Codeception\TestCase $test) {
        $_SESSION = array();
        $_GET     = array();
        $_POST    = array();
        $_COOKIE  = array();
        $fc = $this->bootstrap->getBootstrap()->getResource('frontcontroller');
        if ($fc) {
            $fc->resetInstance();
        }
        \Zend_Layout::resetMvcInstance();
        \Zend_Controller_Action_HelperBroker::resetHelpers();
        \Zend_Session::$_unitTestEnabled = true;
        $this->queries = 0;
        $this->time = 0;
    }

    protected function debugResponse()
    {
//        $this->debugsection('Route', sprintf('%/%/%',
//            $this->client->getzendrequest()->getmodulename(),
//            $this->client->getzendrequest()->getcontrollername(),
//            $this->client->getzendrequest()->getactionname()
//        ));
        $this->debugSection('Session',json_encode($_COOKIE));
        if ($this->db) {
            $profiler = $this->db->getProfiler();
            $queries = $profiler->getTotalNumQueries() - $this->queries;
            $time = $profiler->getTotalElapsedSecs() - $this->time;
            $this->debugSection('Db',$queries.' queries');
            $this->debugSection('Time',round($time,2).' secs taken');
            $this->time = $profiler->getTotalElapsedSecs();
            $this->queries = $profiler->getTotalNumQueries();
        }
    }

}
