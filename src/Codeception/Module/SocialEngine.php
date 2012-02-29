<?php
/**
 * This module allows you to run tests inside Zend Framework.
 * It acts just like ControllerTestCase, but with usage of Codeception syntax.
 * Currently this module is a bit *alpha* as I have a little bit experience with ZF. Thus, contributions are welcome.
 *
 * It assumes, you have standard structure with __APPLICATION_PATH__ set to './application'
 * and LIBRARY_PATH set to './library'. If it's not redefine this constants in bootstrap file of your suite.
 *
 * ## Config
 *
 * * env  - environment used for testing ('testing' by default).
 * * config - relative path to your application config ('application/configs/application.ini' by default).
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
 * Use a generated helper TestHelper. Usse this code inside of it.
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
namespace Codeception\Module;

class SocialEngine extends \Codeception\Util\Framework implements \Codeception\Util\FrameworkInterface
{
    protected $config = array('env' => 'testing', 'config' => 'application/settings/database.php', 'base' => '');
    // 'app_path' => 'application', 'lib_path' => 'library',

    /**
     * @var \Zend_Application
     */
    public $bootstrap;

    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    public $db;

    /**
     * @var \Codeception\Util\Connector\SE
     */
    public $client;

    protected $queries = 0;
    protected $time = 0;

    public function _initialize() {

        define('_ENGINE_R_BASE', '/');
        define('_ENGINE_R_FILE', '/index.php');
        define('_ENGINE_R_REL', 'application');
        define('_ENGINE_R_TARG', 'index.php');

        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        defined('PS') || define('PS', PATH_SEPARATOR);
        defined('_ENGINE') || define('_ENGINE', true);
        defined('_ENGINE_REQUEST_START') || 
            define('_ENGINE_REQUEST_START', microtime(true));

        defined('APPLICATION_PATH') || 
            define('APPLICATION_PATH',     $this->config['base']);
        defined('APPLICATION_PATH_COR') || 
            define('APPLICATION_PATH_COR', APPLICATION_PATH.DS.'application');
        defined('APPLICATION_PATH_EXT') || 
            define('APPLICATION_PATH_EXT', APPLICATION_PATH . DS . 'externals');
        defined('APPLICATION_PATH_PUB') || 
            define('APPLICATION_PATH_PUB', APPLICATION_PATH . DS . 'public');
        defined('APPLICATION_PATH_TMP') || 
            define('APPLICATION_PATH_TMP', APPLICATION_PATH . DS . 'temporary');

        defined('APPLICATION_PATH_BTS') || 
            define('APPLICATION_PATH_BTS', APPLICATION_PATH_COR . DS . 'bootstraps');
        defined('APPLICATION_PATH_LIB') || 
            define('APPLICATION_PATH_LIB', APPLICATION_PATH_COR . DS . 'libraries');
        defined('APPLICATION_PATH_MOD') || 
            define('APPLICATION_PATH_MOD', APPLICATION_PATH_COR . DS . 'modules');
        defined('APPLICATION_PATH_PLU') || 
            define('APPLICATION_PATH_PLU', APPLICATION_PATH_COR . DS . 'plugins');
        defined('APPLICATION_PATH_SET') || 
            define('APPLICATION_PATH_SET', APPLICATION_PATH_COR . DS . 'settings');
        defined('APPLICATION_PATH_WID') || 
            define('APPLICATION_PATH_WID', APPLICATION_PATH_COR . DS . 'widgets');

        defined('APPLICATION_NAME') || define('APPLICATION_NAME', 'Core');
        defined('_ENGINE_ADMIN_NEUTER') || define('_ENGINE_ADMIN_NEUTER', false);
        defined('_ENGINE_NO_AUTH') || define('_ENGINE_NO_AUTH', false);


        // development mode
        $application_env = @$generalConfig['environment_mode'];
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');

        defined('_ENGINE_SSL') || define('_ENGINE_SSL', (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on'));

        // Setup required include paths; optimized for Zend usage. Most other includes
        // will use an absolute path
        set_include_path(
          APPLICATION_PATH_LIB . PS .
          APPLICATION_PATH_LIB . DS . 'PEAR' . PS .
            get_include_path()
        );


        if( file_exists(APPLICATION_PATH_SET . DS . 'general.php') ) {
          $generalConfig = include APPLICATION_PATH_SET . DS . 'general.php';
        } else {
          $generalConfig = array('environment_mode' => 'production');
        }


        // Check for uninstalled state
        if( !file_exists(APPLICATION_PATH_SET . DS . 'database.php') ) {
          if( 'cli' !== PHP_SAPI ) {
            header('Location: ' . rtrim((string)constant('_ENGINE_R_BASE'), '/') . '/install/index.php');
          } else {
            echo 'Not installed' . PHP_EOL;
          }
          exit();
        }

        // Check tasks
        if( !empty($_REQUEST['notrigger']) ) {
          define('ENGINE_TASK_NOTRIGGER', true);
        }

        // Sub apps
        if( !defined('_ENGINE_R_MAIN') && !defined('_ENGINE_R_INIT') ) {
          if( @$_GET['m'] == 'css' ) {
            define('_ENGINE_R_MAIN', 'css.php');
            define('_ENGINE_R_INIT', false);
          } else if( @$_GET['m'] == 'lite' ) {
            define('_ENGINE_R_MAIN', 'lite.php');
            define('_ENGINE_R_INIT', true);
          } else {
            define('_ENGINE_R_MAIN', false);
            define('_ENGINE_R_INIT', true);
          }
        }

        // Boot
        if( _ENGINE_R_INIT ) {
          
          // Application
          require_once APPLICATION_PATH_LIB. DS .'Engine/Loader.php';
          require_once APPLICATION_PATH_LIB. DS .'Engine/Application.php';
        }

        $this->client = new \Codeception\Util\Connector\SocialEngine();
        $this->client->setHost($this->config['host']);
    }

    public function _before(\Codeception\TestCase $test) {
        // Create application, bootstrap, and run
        $this->bootstrap = new \Engine_Application(
            array(
              'environment' => APPLICATION_ENV,
              'bootstrap' => array(
                'path' => APPLICATION_PATH_COR . DS . 'modules' . DS . APPLICATION_NAME . DS . 'Bootstrap.php',
                'class' => ucfirst(APPLICATION_NAME) . '_Bootstrap',
              ),
              'autoloaderNamespaces' => array(
                'Zend'      => APPLICATION_PATH_LIB . DS . 'Zend',
                'Engine'    => APPLICATION_PATH_LIB . DS . 'Engine',
                'Facebook'  => APPLICATION_PATH_LIB . DS . 'Facebook',

                'Bootstrap' => APPLICATION_PATH_BTS,
                'Plugin'    => APPLICATION_PATH_PLU,
                'Widget'    => APPLICATION_PATH_WID,
              ),
            )
          );
        \Zend_Session::$_unitTestEnabled = true;

        \Engine_Application::setInstance($this->bootstrap);
        \Engine_Api::getInstance()->setApplication($this->bootstrap);


        $this->bootstrap->bootstrap(); 
        $this->client->setBootstrap($this->bootstrap);

        // $db = $this->bootstrap->getBootstrap()->getResource('db');
        // if ($db instanceof \Zend_Db_Adapter_Abstract) {
        //     $this->db = $db;
        //     $this->db->getProfiler()->setEnabled(true); 
        //     $this->db->getProfiler()->clear();
        // }
    }

    public function _after(\Codeception\TestCase $test) {
        $_SESSION = array();
        $_GET     = array();
        $_POST    = array();
        $_COOKIE  = array();
        
        
        $this->front = $this->bootstrap->getBootstrap()->getContainer()->frontcontroller->resetInstance();
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
