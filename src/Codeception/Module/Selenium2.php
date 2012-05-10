<?php
/**
 * Uses Mink to manipulate Selenium2 WebDriver
 *
 * Note that all method take CSS selectors to fetch elements.
 *
 * On test failure the browser window screenshot will be saved to log directory
 *
 * ## Installation
 *
 * Download Selenium2 WebDriver from http://code.google.com/p/selenium/downloads/list?q=selenium-server-standalone-2
 * Launch the daemon: ```java -jar selenium-server-standalone-2.xx.xxx.jar```
 *
 * Don't forget to turn on Db repopulation if you are using database.
 *
 * ## Configuration
 *
 * * url *required* - start url for your app
 * * browser *required* - browser that would be launched
 * * host  - Selenium server host (localhost by default)
 * * port - Selenium server port (4444 by default)
 *
 * ## Public Properties
 *
 * * session - contains Mink Session
 */
namespace Codeception\Module;


class Selenium2 extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array('browser', 'url');

    protected $config = array('host' => '127.0.0.1', 'port' => '4444');
    public function _cleanup() {
        $driver = new \Behat\Mink\Driver\Selenium2Driver(
            $this->config['browser'],
            null,
            sprintf('http://%s:%d/wd/hub',$this->config['host'],$this->config['port'])
        );
        $this->session = new \Behat\Mink\Session($driver);
        $this->session->start();
    }

    public function _failed(\Codeception\TestCase $test, $error) {
        $this->session->getDriver()->getBrowser()->captureEntirePageScreenshot(\Codeception\Configuration::logDir().basename($test->getFileName()).'.debug.png','');
        $this->debug("Screenshot was saved into 'log' dir");
        $this->session->stop();
    }


    // please, add more custom Selenium functions here

}
