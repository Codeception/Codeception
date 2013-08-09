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
 * * capabilities - sets Selenium2 [desired capabilities](http://code.google.com/p/selenium/wiki/DesiredCapabilities). Should be a key-value array.
 *
 * ### Example (`acceptance.suite.yml`)
 *
 *     modules:
 *        enabled: [Selenium2]
 *        config:
 *           Selenium2:
 *              url: 'http://localhost/'
 *              browser: firefox
 *              capabilities:
 *                  unexpectedAlertBehaviour: 'accept'
 *
 * ## Public Properties
 *
 * * session - contains Mink Session
 * * webDriverSession - contains webDriverSession object, i.e. $session from [php-webdriver](https://github.com/facebook/php-webdriver)
 */

use Behat\Mink\Driver\Selenium2Driver;
use Codeception\Util\MinkJS;

class Selenium2 extends MinkJS
{
    protected $requiredFields = array('browser', 'url');
    protected $config = array('host' => '127.0.0.1', 'port' => '4444', 'delay' => 0, 'capabilities' => array());

    /**
     * @var \WebDriver\Session
     */
    public $webDriverSession;


    public function _initialize() {
        $capabilities = array_merge(Selenium2Driver::getDefaultCapabilities(), $this->config['capabilities']);
        $capabilities['name'] = 'Codeception Test';
        $driver = new Selenium2Driver(
            $this->config['browser'],
            $capabilities,
            sprintf('http://%s:%d/wd/hub',$this->config['host'],$this->config['port'])
        );
        $this->session = new \Behat\Mink\Session($driver);
    }

    public function _before(\Codeception\TestCase $test)
    {
        if ($this->session) {
            $this->session->start();
            $this->webDriverSession = $this->session->getDriver()->getWebDriverSession();
        }
    }

    public function _failed(\Codeception\TestCase $test, $error) {
        $this->_saveScreenshot(\Codeception\Configuration::logDir().basename($test->getFileName()).'.fail.png');
        $this->debug("Screenshot was saved into 'log' dir");
        $this->session->stop();
    }

    public function _afterStep(\Codeception\Step $step) {
        if ($this->config['delay']) usleep($this->config['delay'] * 1000);
    }

    /**
     * Low-level API method.
     * If Codeception commands are not enough, use Selenium WebDriver methods directly
     *
     * ``` php
     * $I->executeInSelenium(function(\WebDriver\Session $webdriver) {
     *   $webdriver->back();
     * });
     * ```
     *
     * Use [WebDriver Session API](https://github.com/facebook/php-webdriver)
     * Not recommended this command too be used on regular basis.
     * If Codeception lacks important Selenium methods implement then and submit patches.
     *
     * @param callable $function
     */
    public function executeInSelenium(\Closure $function)
    {
        $function($this->webDriverSession);
    }

    public function _saveScreenshot($filename)
    {
        if (!$this->webDriverSession) {
            $this->debug("Can't make screenshot, no web driver");
            return;
        }
        $imgData = base64_decode($this->webDriverSession->screenshot());
        file_put_contents($filename, $imgData);
    }

    public function click($link, $context = null, $strict = false) {
        $url = $this->session->getCurrentUrl(); // required for popups
        $el = $this->findClickable($link, $context, $strict);
        $el->click();
        $this->debugSection('URL', $url);
        $this->debugPageInfo();
    }
    
    protected function debugPageInfo()
    {
        $this->debugSection('Title', $this->webDriverSession->title());
        $this->debugSection('Cookies', json_encode($this->webDriverSession->getAllCookies()));
    }    

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
        $this->webDriverSession->accept_alert();
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
        $this->webDriverSession->dismiss_alert();
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
        $this->assertContains($text, $this->webDriverSession->alert_text());
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
        $this->assertNotContains($text, $this->webDriverSession->alert_text());
    }

    /**
     * Switch to another window identified by its name.
     * 
     * The window can only be identified by its name. If the $name parameter is blank it will switch to the parent window.
     *
     * Example:
     * ``` html
     * <input type="button" value="Open window" onclick="window.open('http://example.com', 'another_window')">
     * ```
     *
     * ``` php
     * <?php
     * $I->click("Open window");
     * # switch to another window
     * $I->switchToWindow("another_window");
     * # switch to parent window
     * $I->switchToWindow();
     * ?>
     * ```
     *
     * If the window has no name, the only way to access it is via the `executeInSelenium()` method like so:
     * 
     * ```
     * <?php
     * $I->executeInSelenium(function (\Webdriver\Session $webdriver) {
     * $handles=$webdriver->window_handles();
     * $last_window = end($handles);
     * $webdriver->focusWindow($last_window);
     * });
     * ?>
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

    /**
     * Resize current window
     *
     * Example:
     * ``` php
     * <?php
     * $I->resizeWindow(800, 600);
     *
     * ```
     *
     * @param int    $width
     * @param int    $height
     * @author Jaik Dean <jaik@jaikdean.com>
     */
    public function resizeWindow($width, $height) {
        $this->webDriverSession->window('current')->postSize(array('width' => $width, 'height' => $height));
    }

    public function seeInTitle($title)
    {
        $this->assertContains($title, $this->webDriverSession->title(), "page title contains $title");
    }

    public function dontSeeInTitle($title)
    {
        $this->assertNotContains($title, $this->webDriverSession->title(), "page title contains $title");
    }

    public function seeTitleIs()
    {

    }

}
