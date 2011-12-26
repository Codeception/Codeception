<?php
/**
 * Uses Mink to launch and manipulate Selenium Server (formerly the Selenium RC Server).
 *
 * ## Configuration
 *
 * * url *required* - start url for your app
 * * browser *required* - browser that would be launched
 * * host  - Selenium server host
 * * port - Selenium server port
 *
 * ## Public Properties
 *
 * * session - contains Mink Session
 */
namespace Codeception\Module;

class Selenium extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array('browser', 'url');
    
    protected $config = array('host' => '127.0.0.1', 'port' => '4444');
    
    public function _cleanup() {
        $client = new \Selenium\Client($this->config['host'], $this->config['port']);
        $driver = new \Behat\Mink\Driver\SeleniumDriver(
            $this->config['browser'], $this->config['url'], $client
        );

        $this->session = new \Behat\Mink\Session($driver);
        $this->session->start();
    }

    public function _failed(\Codeception\TestCase $test, $error) {
        $this->session->getDriver()->getBrowser()->captureEntirePageScreenshot(\Codeception\Configuration::logDir().$test->getFileName().'.debug.png','');
//        $this->debug($this->session->getDriver()->getBrowser()->retrieveLastRemoteControlLogs());
        $this->debug("Screenshot was saved into 'log' dir");
    }

    // please, add more custom Selenium functions here

}
