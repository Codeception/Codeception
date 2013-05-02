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

use Behat\Mink\Driver\SeleniumDriver;
use Codeception\Util\MinkJS;

class Selenium extends MinkJS
{
    protected $requiredFields = array('browser', 'url');

    protected $config = array('host' => '127.0.0.1', 'port' => '4444', 'delay' => 0);


    public function _initialize() {
        $client = new \Selenium\Client($this->config['host'], $this->config['port']);
        $driver = new SeleniumDriver(
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

    /*
     * INHERITED ACTIONS
     */

    public function amOnPage($page)
    {
        parent::amOnPage($page);
    }

    public function dontSee($text, $selector = null)
    {
        parent::dontSee($text, $selector);
    }

    public function see($text, $selector = null)
    {
        parent::see($text, $selector);
    }

    public function seeLink($text, $url = null)
    {
        parent::seeLink($text, $url);
    }

    public function dontSeeLink($text, $url = null)
    {
        parent::dontSeeLink($text, $url);
    }

    public function click($link, $context = null)
    {
        parent::click($link, $context);
    }

    public function dontSeeElement($selector)
    {
        parent::dontSeeElement($selector);
    }

    public function reloadPage()
    {
        parent::reloadPage();
    }

    public function moveBack()
    {
        parent::moveBack();
    }

    public function moveForward()
    {
        parent::moveForward();
    }

    public function fillField($field, $value)
    {
        parent::fillField($field, $value);
    }

    public function selectOption($select, $option)
    {
        parent::selectOption($select, $option);
    }

    public function seeInCurrentUrl($uri)
    {
        parent::seeInCurrentUrl($uri);
    }

    public function dontSeeInCurrentUrl($uri)
    {
        parent::dontSeeInCurrentUrl($uri);
    }

    public function seeCurrentUrlEquals($uri)
    {
        parent::seeCurrentUrlEquals($uri);
    }

    public function dontSeeCurrentUrlEquals($uri)
    {
        parent::dontSeeCurrentUrlEquals($uri);
    }

    public function seeCurrentUrlMatches($uri)
    {
        parent::seeCurrentUrlMatches($uri);
    }

    public function dontSeeCurrentUrlMatches($uri)
    {
        parent::dontSeeCurrentUrlMatches($uri);
    }

    public function grabFromCurrentUrl($uri = null)
    {
        return parent::grabFromCurrentUrl($uri);
    }

    public function attachFile($field, $filename)
    {
        parent::attachFile($field, $filename);
    }

    public function seeCheckboxIsChecked($checkbox)
    {
        parent::seeCheckboxIsChecked($checkbox);
    }

    public function dontSeeCheckboxIsChecked($checkbox)
    {
        parent::dontSeeCheckboxIsChecked($checkbox);
    }

    public function seeInField($field, $value)
    {
        parent::seeInField($field, $value);
    }

    public function dontSeeInField($field, $value)
    {
        parent::dontSeeInField($field, $value);
    }

    public function grabTextFrom($cssOrXPathOrRegex)
    {
        return parent::grabTextFrom($cssOrXPathOrRegex);
    }

    public function grabValueFrom($field)
    {
        return parent::grabValueFrom($field);
    }

    public function grabAttribute()
    {
        parent::grabAttribute();
    }

    public function checkOption($option)
    {
        parent::checkOption($option);
    }

    public function uncheckOption($option)
    {
        parent::uncheckOption($option);
    }

    public function doubleClick($link)
    {
        parent::doubleClick($link);
    }

    public function clickWithRightButton($link)
    {
        parent::clickWithRightButton($link);
    }

    public function moveMouseOver($link)
    {
        parent::moveMouseOver($link);
    }

    public function focus($el)
    {
        parent::focus($el);
    }

    public function blur($el)
    {
        parent::blur($el);
    }

    public function dragAndDrop($el1, $el2)
    {
        parent::dragAndDrop($el1, $el2);
    }

    public function seeElement($selector)
    {
        parent::seeElement($selector);
    }

    public function pressKey($element, $char, $modifier = null)
    {
        parent::pressKey($element, $char, $modifier);
    }

    public function pressKeyUp($element, $char, $modifier = null)
    {
        parent::pressKeyUp($element, $char, $modifier);
    }

    public function pressKeyDown($element, $char, $modifier = null)
    {
        parent::pressKeyDown($element, $char, $modifier);
    }

    public function wait($milliseconds)
    {
        parent::wait($milliseconds);
    }

    public function waitForJS($milliseconds, $jsCondition)
    {
        parent::waitForJS($milliseconds, $jsCondition);
    }

    public function executeJs($jsCode)
    {
        parent::executeJs($jsCode);
    }
}
