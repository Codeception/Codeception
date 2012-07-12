<?php
namespace Codeception\Module;

/**
 * Uses Mink to launch and manipulate Selenium Server (formerly the Selenium RC Server).
 *
 * Note, all method takes CSS selectors to fetch elements.
 * For links, buttons, fields you can use names/values/ids of elements.
 * For form fields you can use name of matched label.
 *
 * Will save a screenshot of browser window to log directory on fail.
 *
 * ## Installation
 *
 * [Download Selenium RC Server](http://seleniumhq.org/download)
 *
 * Execute it: `java -jar selenium-server-standalone-x.xx.xxx.jar`
 *
 * Best used with Firefox browser.
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

class Selenium extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array('browser', 'url');

    protected $config = array('host' => '127.0.0.1', 'port' => '4444', 'delay' => 0);


    public function _cleanup() {
        $client = new \Selenium\Client($this->config['host'], $this->config['port']);
        $driver = new \Behat\Mink\Driver\SeleniumDriver(
            $this->config['browser'], $this->config['url'], $client
        );

        $this->session = new \Behat\Mink\Session($driver);
        $this->session->start();
    }

    public function _failed(\Codeception\TestCase $test, $error) {
        $this->_saveScreenshot(\Codeception\Configuration::logDir().basename($test->getFileName()).'.fail.png');
        $this->debug("Screenshot was saved into 'log' dir");
        $this->session->stop();
    }

    // please, add more custom Selenium functions here
    public function _afterStep(\Codeception\Step $step) {
        if ($this->config['delay']) usleep($this->config['delay'] * 1000);
    }

    public function _saveScreenshot($filename)
    {
        $this->session->getDriver()->getBrowser()->captureEntirePageScreenshot($filename,'');
    }

}
