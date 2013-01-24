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
 * Download [Selenium2 WebDriver](http://code.google.com/p/selenium/downloads/list?q=selenium-server-standalone-2)
 * Launch the daemon: `java -jar selenium-server-standalone-2.xx.xxx.jar`
 *
 * Don't forget to turn on Db repopulation if you are using database.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * stability: stable
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
 * ## Public Properties
 *
 * * session - contains Mink Session
 */

class Selenium2 extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array('browser', 'url');
    protected $config = array('host' => '127.0.0.1', 'port' => '4444', 'delay' => 0);


    public function _initialize() {
        $driver = new \Behat\Mink\Driver\Selenium2Driver(
            $this->config['browser'],
            null,
            sprintf('http://%s:%d/wd/hub',$this->config['host'],$this->config['port'])
        );
        $this->session = new \Behat\Mink\Session($driver);
        parent::_initialize();
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

    /**
     * Accept alert or confirm popup
     *
     * Example:
     * ``` php
     * <?php
     * $I->click('Show alert popup');
     * $I->acceptPopup();
     *
     * ```
     */
    public function acceptPopup() {
        $this->session->getDriver()->wdSession->accept_alert();
    }

    /**
     * Dismiss alert or confirm popup
     *
     * Example:
     * ``` php
     * <?php
     * $I->click('Show confirm popup');
     * $I->cancelPopup();
     *
     * ```
     */
    public function cancelPopup() {
        $this->session->getDriver()->wdSession->dismiss_alert();
    }

    /**
     * Checks if popup contains the $text
     *
     * Example:
     * ``` php
     * <?php
     * $I->click('Show alert popup');
     * $I->seeInPopup('Error message');
     *
     * ```
     *
     * @param string $text
     */
    public function seeInPopup($text) {
        $this->assertContains($text, $this->session->getDriver()->wdSession->alert_text());
    }

    /**
     * Check if popup don't contains the $text
     *
     * Example:
     * ``` php
     * <?php
     * $I->click();
     * $I->dontSeeInPopup('Error message');
     *
     * ```
     *
     * @param string $text
     */
    public function dontSeeInPopup($text) {
        $this->assertNotContains($text, $this->session->getDriver()->wdSession->alert_text());
    }

    /**
     * Switch to another window
     *
     * Example:
     * ``` html
     * <input type="button" value="Open window" onclick="window.open('http://example.com', 'another_window')">
     *
     * ```
     *
     * ``` php
     * <?php
     * $I->click("Open window");
     * # switch to another window
     * $I->switchToWindow("another_window");
     * # switch to parent window
     * $I->switchToWindow();
     *
     * ```
     *
     * @param string|null $name
     */
    public function switchToWindow($name = null) {
        $this->session->getDriver()->switchToWindow($name);
    }

    /**
     * Switch to another frame
     *
     * Example:
     * ``` html
     * <iframe name="another_frame" src="http://example.com">
     *
     * ```
     *
     * ``` php
     * <?php
     * # switch to iframe
     * $I->switchToIFrame("another_frame");
     * # switch to parent page
     * $I->switchToIFrame();
     *
     * ```
     *
     * @param string|null $name
     */
    public function switchToIFrame($name = null) {
        $this->session->getDriver()->switchToIFrame($name);
    }

}
