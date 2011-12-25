<?php
/**
 * Uses Mink to launch and manipulates Selenium Server (formerly the Selenium RC Server).
 *
 * ## Configuration
 *
 * * url *required* - start url for your app
 * * browser *required* - browser that would be launched
 * * host *required* - Selenium server host
 * * port *required* - Selenium server port
 */
namespace Codeception\Module;

class Selenium extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array('host', 'port', 'browser', 'url');
    
    public function _cleanup() {
        $client = new \Selenium\Client($this->config['host'], $this->config['port']);
        $driver = new \Behat\Mink\Driver\SeleniumDriver(
            $this->config['browser'], $this->config['url'], $client
        );
        $this->session = new \Behat\Mink\Session($driver);
    }
}
