<?php
namespace Codeception\Module;

/**
 *
 * Uses Mink to launch and manipulate Selenium Server (formerly the Selenium RC Server).
 *
 * But we recommend you to use **Selenium 2 WebDriver** as it is an evolution of SeleniumRC and grants you more stable results.
 * For manipulation with Selenium WebDriver use [Selenium2](/docs/modules/Selenium2) module
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
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 * * relies on [Mink](http://mink.behat.org)
 *
 * ## Configuration
 *
 * * url *required* - start url for your app
 * * browser *required* - browser that would be launched
 * * host  - Selenium server host (localhost by default)
 * * port - Selenium server port (4444 by default)
 * * delay - set delay between actions in milliseconds (1/1000 of second) if they run too fast
 *
 * ### Example (`acceptance.suite.yml`)
 *
 *     modules: 
 *        enabled: [Selenium]
 *        config:
 *           Selenium:
 *              url: 'http://localhost/' 
 *              browser: firefox 
 *
 * ## Public Properties
 *
 * * session - contains Mink Session
 */

class Selenium extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array('browser', 'url');

    protected $config = array('host' => '127.0.0.1', 'port' => '4444', 'delay' => 0);


    public function _initialize() {
        $client = new \Selenium\Client($this->config['host'], $this->config['port']);
        $driver = new \Behat\Mink\Driver\SeleniumDriver(
            $this->config['browser'], $this->config['url'], $client
        );

        $this->session = new \Behat\Mink\Session($driver);

    }

    public function _failed(\Codeception\TestCase $test, $error) {
        $this->_saveScreenshot(\Codeception\Configuration::logDir().basename($test->getFileName()).'.fail.png');
        $this->debug("Screenshot was saved into 'log' dir");
        $this->session->stop();
    }

    /**
     * Low-level API method.
     * If Codeception commands are not enough, use Selenium RC methods directly
     *
     * ``` php
     * $I->executeInSelenium(function(\Selenium\Browser $browser) {
     *   $browser->open('/');
     * });
     * ```
     *
     * Use [Browser Selenium API](https://github.com/alexandresalome/PHP-Selenium)
     * Not recommended this command too be used on regular basis.
     * If Codeception lacks important Selenium methods implement then and submit patches.
     *
     * @param callable $function
     */
    public function executeInSelenium(\Closure $function)
    {
        $function($this->session->getDriver()->getBrowser());
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
