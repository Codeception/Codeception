<?php
namespace Codeception\Module;

/**
 * Uses Mink to manipulate Selenium2 WebDriver
 *
 * Note that all method take CSS selectors to fetch elements.
 *
 * On test failure the browser window screenshot will be saved to log directory
 *
 * ## Installation
 *
 * Download Selenium2 [WebDriver](http://code.google.com/p/selenium/downloads/list?q=selenium-server-standalone-2)
 * Launch the daemon: `java -jar selenium-server-standalone-2.xx.xxx.jar`
 *
 * Don't forget to turn on Db repopulation if you are using database.
 *
 * ## Configuration
 *
 * * url *required* - start url for your app
 * * browser *required* - browser that would be launched
 * * host  - Selenium server host (localhost by default)
 * * port - Selenium server port (4444 by default)
 * * delay - set delay between actions in milliseconds (1/1000 of second) if they run too fast
 *
 * ## Public Properties
 *
 * * session - contains Mink Session
 */

class Selenium2 extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array('browser', 'url');
    protected $config = array('host' => '127.0.0.1', 'port' => '4444', 'delay' => 0);


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
        $this->_saveScreenshot(\Codeception\Configuration::logDir().basename($test->getFileName()).'.fail.png');
        $this->debug("Screenshot was saved into 'log' dir");
        $this->session->stop();
    }

    public function _afterStep(\Codeception\Step $step) {
        if ($this->config['delay']) usleep($this->config['delay'] * 1000);
    }

    public function _saveScreenshot($filename)
    {
        if (!isset($this->session->getDriver()->wdSession)) {
            $this->debug("Can't make screenshot, no web driver");
            return;
        }
        $wd = $this->session->getDriver()->wdSession;
        $imgData = base64_decode($wd->screenshot());
        file_put_contents($filename, $imgData);
    }

    // please, add more custom Selenium functions here

}
