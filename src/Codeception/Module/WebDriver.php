<?php
namespace Codeception\Module;

use Codeception\Exception\ElementNotFound;
use Codeception\Exception\TestRuntime;
use Codeception\Util\Debug;
use Codeception\Util\Locator;
use Codeception\Util\WebInterface;
use Codeception\Util\RemoteInterface;
use Symfony\Component\DomCrawler\Crawler;
use Codeception\PHPUnit\Constraint\WebDriver as WebDriverConstraint;
use Codeception\PHPUnit\Constraint\WebDriverNot as WebDriverConstraintNot;
use Codeception\PHPUnit\Constraint\Page as PageConstraint;

/**
 * New generation Selenium2 module.
 * *Included in Codeception 1.7.0*
 *
 * ## Installation
 *
 * Download [Selenium2 WebDriver](http://code.google.com/p/selenium/downloads/list?q=selenium-server-standalone-2)
 * Launch the daemon: `java -jar selenium-server-standalone-2.xx.xxx.jar`
 *
 * ## Migration Guide (Selenium2 -> WebDriver)
 *
 * * `wait` method accepts seconds instead of milliseconds. All waits use second as parameter.
 *
 *
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **beta**
 * * Contact: davert.codecept@mailican.com
 * * Based on [facebook php-webdriver](https://github.com/facebook/php-webdriver)
 *
 * ## Configuration
 *
 * * url *required* - start url for your app
 * * browser *required* - browser that would be launched
 * * host  - Selenium server host (localhost by default)
 * * port - Selenium server port (4444 by default)
 * * restart - set to false to share browser sesssion between tests (by default), or set to true to create a session per test
 * * wait - set the implicit wait (5 secs) by default.
 * * capabilities - sets Selenium2 [desired capabilities](http://code.google.com/p/selenium/wiki/DesiredCapabilities). Should be a key-value array.
 *
 * ### Example (`acceptance.suite.yml`)
 *
 *     modules:
 *        enabled: [WebDriver]
 *        config:
 *           WebDriver:
 *              url: 'http://localhost/'
 *              browser: firefox
 *              wait: 10
 *              capabilities:
 *                  unexpectedAlertBehaviour: 'accept'
 *
 *
 * Class WebDriver
 * @package Codeception\Module
 */
class WebDriver extends \Codeception\Module implements WebInterface, RemoteInterface {

    protected $requiredFields = array('browser', 'url');
    protected $config = array(
        'host' => '127.0.0.1',
        'port' => '4444',
        'restart' => false,
        'wait' => 0,
        'capabilities' => array()
    );

    protected $wd_host;
    protected $capabilities;
    protected $test;

    /**
     * @var \RemoteWebDriver
     */
    public $webDriver;

    public function _initialize()
    {
        $this->wd_host =  sprintf('http://%s:%s/wd/hub', $this->config['host'], $this->config['port']);
        $this->capabilities = $this->config['capabilities'];
        $this->capabilities[\WebDriverCapabilityType::BROWSER_NAME] = $this->config['browser'];
        $this->webDriver = \RemoteWebDriver::create($this->wd_host, $this->capabilities);
        $this->webDriver->manage()->timeouts()->implicitlyWait($this->config['wait']);
    }

    public function _before(\Codeception\TestCase $test)
    {
        if (!isset($this->webDriver)) {
            $this->_initialize();
        }
        $this->test=$test;
    }

    public function _after(\Codeception\TestCase $test)
    {
        if ($this->config['restart'] && isset($this->webDriver)) {
            $this->webDriver->quit();
            // \RemoteWebDriver consists of four parts, executor, mouse, keyboard and touch, quit only set executor to null,
            // but \RemoteWebDriver doesn't provide public access to check on executor
            // so we need to unset $this->webDriver here to shut it down completely
            $this->webDriver = null;
        }
    }

    public function _failed(\Codeception\TestCase $test, $fail)
    {
        $this->_saveScreenshot(\Codeception\Configuration::logDir().basename($test->getFileName()).'.fail.png');
        $this->debug("Screenshot was saved into 'log' dir");
    }

    public function _afterSuite()
    {
        // this is just to make sure webDriver is cleared after suite
        if (isset($this->webDriver)) {
            $this->webDriver->quit();
            unset($this->webDriver);
        }
    }

    public function _getResponseCode() {}

    public function _sendRequest($url) {
        $this->webDriver->get($this->_getUrl().'');
    }

    public function amOnSubdomain($subdomain)
    {
        $url = $this->config['url'];
        $url = preg_replace('~(https?:\/\/)(.*\.)(.*\.)~', "$1$3", $url); // removing current subdomain
        $url = preg_replace('~(https?:\/\/)(.*)~', "$1$subdomain.$2", $url); // inserting new
        $this->_reconfigure(array('url' => $url));
    }

    public function _getUrl()
    {
        if (!isset($this->config['url']))
            throw new \Codeception\Exception\ModuleConfig(__CLASS__, "Module connection failure. The URL for client can't bre retrieved");
        return $this->config['url'];
    }

    public function _getCurrentUri()
    {
        $url = $this->webDriver->getCurrentURL();
        $parts = parse_url($url);
        if (!$parts) $this->fail("URL couldn't be parsed");
        $uri = "";
        if (isset($parts['path'])) $uri .= $parts['path'];
        if (isset($parts['query'])) $uri .= "?".$parts['query'];
        if (isset($parts['fragment'])) $uri .= "#".$parts['fragment'];
        return $uri;
    }

    public function _saveScreenshot($filename)
    {
        $this->webDriver->takeScreenshot($filename);
    }

    /**
     * Makes a screenshot of current window and saves it to `tests/_log/debug`.
     *
     * ``` php
     * <?php
     * $I->amOnPage('/user/edit');
     * $I->makeScreenshot('edit page');
     * // saved to: tests/_log/debug/UserEdit - edit page.png
     * ?>
     * ```
     *
     * @param $name
     */
    public function makeScreenshot($name)
    {
        $debugDir = \Codeception\Configuration::logDir().'debug';
        if (!is_dir($debugDir)) mkdir($debugDir, 0777);
        $caseName = str_replace('Cept.php', '', $this->test->getFileName());
        $caseName = str_replace('Cept.php', '', $caseName);
        /**
         * This is used for Cept only
         *
         * To be consistent with Cest, no sub-dir would be created, '\' and '/' in $caseName would be replaced with '.'
         */
        $search = array('/', '\\');
        $replace = array('.', '.');
        $caseName = str_replace($search, $replace, $caseName);

        $screenName = $debugDir . DIRECTORY_SEPARATOR . $caseName.' - '.$name.'.png';
        $this->_saveScreenshot($screenName);
        $this->debug("Screenshot saved to $screenName");
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
     */
    public function resizeWindow($width, $height) {
        $this->webDriver->manage()->window()->setSize(new \WebDriverDimension($width, $height));
    }

    public function seeCookie($cookie)
    {
        $cookies = $this->webDriver->manage()->getCookies();
        $cookies = array_map(function($c) { return $c['name']; }, $cookies);
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
        $this->assertContains($cookie, $cookies);
    }

    public function dontSeeCookie($cookie)
    {
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
        $this->assertNull($this->webDriver->manage()->getCookieNamed($cookie));
    }

    public function setCookie($cookie, $value)
    {
        $this->webDriver->manage()->addCookie(array('name' => $cookie, 'value' => $value));
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
    }

    public function resetCookie($cookie)
    {
        $this->webDriver->manage()->deleteCookieNamed($cookie);
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
    }

    public function grabCookie($cookie)
    {
        $value = $this->webDriver->manage()->getCookieNamed($cookie);
        if (is_array($value)) return $value['value'];
    }

    public function amOnPage($page)
    {
        $host = rtrim($this->config['url'], '/');
        $page = ltrim($page, '/');
        $this->webDriver->get($host . '/' . $page);
    }

    public function see($text, $selector = null)
    {
        if (!$selector) return $this->assertPageContains($text);
        $nodes = $this->match($this->webDriver, $selector);
        $this->assertNodesContain($text, $nodes, $selector);
    }

    public function dontSee($text, $selector = null)
    {
        if (!$selector) return $this->assertPageNotContains($text);
        $nodes = $this->match($this->webDriver, $selector);
        $this->assertNodesNotContain($text, $nodes, $selector);
    }

    public function click($link, $context = null)
    {
        $page = $this->webDriver;
        if ($context) {
            $nodes = $this->match($this->webDriver, $context);
            if (empty($nodes)) throw new ElementNotFound($context,'CSS or XPath');
            $page = reset($nodes);
        }
        $el = $this->findClickable($page, $link);
        if (!$el) {
            $els = $this->match($page, $link);
            $el = reset($els);
        }
        if (!$el) throw new ElementNotFound($link, 'Link or Button or CSS or XPath');
        $el->click();
    }

    /**
     * @param $page
     * @param $link
     * @return \WebDriverElement
     */
    protected function findClickable($page, $link)
    {
        $locator = Crawler::xpathLiteral(trim($link));

        // narrow
        $xpath = Locator::combine(
            ".//a[normalize-space(.)=$locator]",
            ".//button[normalize-space(.)=$locator]",
            ".//a/img[normalize-space(@alt)=$locator]/ancestor::a",
            ".//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][normalize-space(@value)=$locator]"
        );

        $els = $page->findElements(\WebDriverBy::xpath($xpath));
        if (count($els)) return reset($els);

        // wide
        $xpath = Locator::combine(
            ".//a[./@href][((contains(normalize-space(string(.)), $locator)) or .//img[contains(./@alt, $locator)])]",
            ".//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][contains(./@value, $locator)]",
            ".//input[./@type = 'image'][contains(./@alt, $locator)]",
            ".//button[contains(normalize-space(string(.)), $locator)]"
        );

        $els = $page->findElements(\WebDriverBy::xpath($xpath));
        if (count($els)) return reset($els);
        return null;
    }

    /**
     * @param $selector
     * @return \WebDriverElement
     * @throws \Codeception\Exception\ElementNotFound
     */
    protected function findField($selector)
    {
        if ($selector instanceof \WebDriverElement) return $selector;
        $locator = Crawler::xpathLiteral(trim($selector));

        $xpath = Locator::combine(
            ".//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')][(((./@name = $locator) or ./@id = //label[contains(normalize-space(string(.)), $locator)]/@for) or ./@placeholder = $locator)]",
            ".//label[contains(normalize-space(string(.)), $locator)]//.//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')]"
        );

        $els = $this->webDriver->findElements(\WebDriverBy::xpath($xpath));
        if (count($els)) return reset($els);

        $els = $this->match($this->webDriver, $selector);
        if (count($els)) return reset($els);

        throw new ElementNotFound($selector, "Field by name, label, CSS or XPath");
    }

    public function seeLink($text, $url = null)
    {
        $nodes = $this->webDriver->findElements(\WebDriverBy::partialLinkText($text));
        if (!$url) return $this->assertNodesContain($text, $nodes, 'a');
        $nodes = array_filter($nodes, function(\WebDriverElement $e) use ($url) {
                $parts = parse_url($url);
                if (!$parts) $this->fail("Link URL of '$url' couldn't be parsed");
                $uri = "";
                if (isset($parts['path'])) $uri .= $parts['path'];
                if (isset($parts['query'])) $uri .= "?".$parts['query'];
                if (isset($parts['fragment'])) $uri .= "#".$parts['fragment'];
                return $uri == trim($url);
        });
        $this->assertNodesContain($text, $nodes, "a[href=$url]");
    }


    public function dontSeeLink($text, $url = null)
    {
        $nodes = $this->webDriver->findElements(\WebDriverBy::partialLinkText($text));
        if (!$url) {
            $this->assertNodesNotContain($text, $nodes, 'a');
            return;
        }
        $nodes = array_filter($nodes, function(\WebDriverElement $e) use ($url) {
            return trim($e->getAttribute('href')) == trim($url);
        });
        $this->assertNodesNotContain($text, $nodes,  "a[href=$url]");
    }

    public function seeInCurrentUrl($uri)
    {
        $this->assertContains($uri, $this->_getCurrentUri());
    }

    public function seeCurrentUrlEquals($uri)
    {
        $this->assertEquals($uri, $this->_getCurrentUri());
    }

    public function seeCurrentUrlMatches($uri)
    {
        \PHPUnit_Framework_Assert::assertRegExp($uri, $this->_getCurrentUri());
    }

    public function dontSeeInCurrentUrl($uri)
    {
        $this->assertNotContains($uri, $this->_getCurrentUri());
    }

    public function dontSeeCurrentUrlEquals($uri)
    {
        $this->assertNotEquals($uri, $this->_getCurrentUri());
    }

    public function dontSeeCurrentUrlMatches($uri)
    {
        \PHPUnit_Framework_Assert::assertNotRegExp($uri, $this->_getCurrentUri());
    }

    public function grabFromCurrentUrl($uri = null)
    {
        if (!$uri) {
            return $this->_getCurrentUri();
        }
        $matches = array();
        $res = preg_match($uri, $this->_getCurrentUri(), $matches);
        if (!$res) $this->fail("Couldn't match $uri in ".$this->_getCurrentUri());
        if (!isset($matches[1])) $this->fail("Nothing to grab. A regex parameter required. Ex: '/user/(\\d+)'");
        return $matches[1];
    }

    public function seeCheckboxIsChecked($checkbox)
    {
        $this->assertTrue($this->findField($checkbox)->isSelected());
    }

    public function dontSeeCheckboxIsChecked($checkbox)
    {
        $this->assertFalse($this->findField($checkbox)->isSelected());
    }

    public function seeInField($field, $value)
    {
        $el = $this->findField($field);
        if (!$el) throw new ElementNotFound($field, "Field by name, label, CSS or XPath");
        $el_value = $el->getTagName() == 'textarea'
            ? $el->getText()
            : $el->getAttribute('value');
        $this->assertEquals($value, $el_value);
    }

    public function dontSeeInField($field, $value)
    {
        $el = $this->findField($field);
        $el_value = $el->getTagName() == 'textarea'
            ? $el->getText()
            : $el->getAttribute('value');
        $this->assertNotEquals($value, $el_value);
    }

    public function selectOption($select, $option)
    {
        $el = $this->findField($select);
        if ($el->getTagName() != 'select') {
            $els = $this->matchCheckables($select);
            $radio = null;
            foreach ($els as $el) {
                $radio = $this->findCheckable($el, $option, true);
                if ($radio) break;
            }
            if (!$radio) throw new ElementNotFound($select, "Radiobutton with value or name '$option in");
            $radio->click();
            return;
        }

        $wdSelect = new \WebDriverSelect($el);
        if ($wdSelect->isMultiple()) {
            $wdSelect->deselectAll();
        }
        if (!is_array($option)) {
            $option = array($option);
        }

        $matched = false;

        foreach ($option as $opt) {
            try {
                $wdSelect->selectByVisibleText($opt);
                $matched = true;
            } catch (\NoSuchElementWebDriverError $e) {}
        }
        if ($matched) return;
        foreach ($option as $opt) {
            try {
                $wdSelect->selectByValue($opt);
                $matched = true;
            } catch (\NoSuchElementWebDriverError $e) {}
        }
        if ($matched) return;
        throw new ElementNotFound(json_encode($option), "Option inside $select matched by name or value");
    }

    /*
    * Unselect an option in a select box
    *
    */

    public function unselectOption($select, $option)
    {
        $el = $this->findField($select);

        $wdSelect = new \WebDriverSelect($el);

        if (!is_array($option)) $option = array($option);

        $matched = false;

        foreach ($option as $opt) {
            try {
                $wdSelect->deselectByVisibleText($opt);
                $matched = true;
            } catch (\NoSuchElementWebDriverError $e) {}

            try {
                $wdSelect->deselectByValue($opt);
                $matched = true;
            } catch (\NoSuchElementWebDriverError $e) {}

        }

        if ($matched) return;
        throw new ElementNotFound(json_encode($option), "Option inside $select matched by name or value");
    }
    /**
     * @param $context
     * @param $radio_or_checkbox
     * @param bool $byValue
     * @return mixed|null
     */
    protected function findCheckable($context, $radio_or_checkbox, $byValue = false)
    {
        if ($radio_or_checkbox instanceof \WebDriverElement) return $radio_or_checkbox;

        $locator = Crawler::xpathLiteral($radio_or_checkbox);
        $xpath = Locator::combine(
            "//input[./@type = 'checkbox'][(./@id = //label[contains(normalize-space(string(.)), $locator)]/@for) or ./@placeholder = $locator]",
            "//label[contains(normalize-space(string(.)), $locator)]//.//input[./@type = 'checkbox']",
            "//input[./@type = 'radio'][(./@id = //label[contains(normalize-space(string(.)), $locator)]/@for) or ./@placeholder = $locator]",
            "//label[contains(normalize-space(string(.)), $locator)]//.//input[./@type = 'radio']"
        );
        if ($byValue) {
            $xpath = Locator::combine(
                $xpath,
                "//input[./@type = 'checkbox'][./@value = $locator]",
                "//input[./@type = 'radio'][./@value = $locator]"
            );
        }
        /** @var $context \WebDriverElement  **/
        $els = $context->findElements(\WebDriverBy::xpath($xpath));
        if (count($els)) return reset($els);
        $els = $this->match($context, $radio_or_checkbox);
        if (count($els)) return reset($els);
        return null;
    }

    protected function matchCheckables($selector)
    {
        $els = $this->match($this->webDriver, $selector);
        if (!count($els)) throw new ElementNotFound($selector, "Element containing radio by CSS or XPath");
        return $els;
    }

    public function checkOption($option)
    {
        $field = $this->findCheckable($this->webDriver, $option);
        if (!$field) throw new ElementNotFound($option, "Checkbox or Radio by Label or CSS or XPath");
        if ($field->isSelected()) return;
        $field->click();
    }

    public function uncheckOption($option)
    {
        $field = $this->findCheckable($this->webDriver, $option);
        if (!$field) throw new ElementNotFound($option, "Checkbox by Label or CSS or XPath");
        if (!$field->isSelected()) return;
        $field->click();
    }

    public function fillField($field, $value)
    {
        $el = $this->findField($field);
        $el->clear();
        $el->sendKeys($value);
    }

    public function attachFile($field, $filename)
    {
        $el = $this->findField($field);
        // in order to be compatible on different OS
        $filePath = realpath(\Codeception\Configuration::dataDir().$filename);
        $el->sendKeys($filePath);
    }

    public function grabTextFrom($cssOrXPathOrRegex)
    {
        $els = $this->match($this->webDriver, $cssOrXPathOrRegex);
        if (count($els)) return $els[0]->getText();
        if (@preg_match($cssOrXPathOrRegex, $this->webDriver->getPageSource(), $matches)) return $matches[1];
        throw new ElementNotFound($cssOrXPathOrRegex, 'CSS or XPath or Regex');
    }

    public function grabValueFrom($field)
    {
        $el = $this->findField($field);
        // value of multiple select is the value of the first selected option
        if ($el->getTagName() == 'select') {
            $select = new \WebDriverSelect($el);
            return $select->getFirstSelectedOption()->getAttribute('value');
        }
        return $el->getAttribute('value');
    }

    /**
     * Checks for a visible element on a page, matching it by CSS or XPath
     *
     * ``` php
     * <?php
     * $I->seeElement('.error');
     * $I->seeElement('//form/input[1]');
     * ?>
     * ```
     * @param $selector
     */
    public function seeElement($selector)
    {
        $els = array_filter($this->match($this->webDriver, $selector), function(\WebDriverElement $el) {
            return $el->isDisplayed();
        });
        $this->assertNotEmpty($els);
    }

    /**
     * Checks that element is invisible or not present on page.
     *
     * ``` php
     * <?php
     * $I->dontSeeElement('.error');
     * $I->dontSeeElement('//form/input[1]');
     * ?>
     * ```
     *
     * @param $selector
     */
    public function dontSeeElement($selector)
    {
        $els = array_filter($this->match($this->webDriver, $selector), function(\WebDriverElement $el) {
            return $el->isDisplayed();
        });
        $this->assertEmpty($els);
    }

    /**
     * Checks if element exists on a page even it is invisible.
     *
     * ``` php
     * <?php
     * $I->seeElementInDOM('//form/input[type=hidden]');
     * ?>
     * ```
     *
     * @param $selector
     */
    public function seeElementInDOM($selector)
    {
        $this->assertNotEmpty($this->match($this->webDriver, $selector));
    }


    /**
     * Opposite to `seeElementInDOM`.
     *
     * @param $selector
     */
    public function dontSeeElementInDOM($selector)
    {
        $this->assertEmpty($this->match($this->webDriver, $selector));
    }

    public function seeOptionIsSelected($selector, $optionText)
    {
        $el = $this->findField($selector);
        if ($el->getTagName() !== 'select') {
            $els = $this->matchCheckables($selector);
            foreach ($els as $k => $el) {
                $els[$k] = $this->findCheckable($el, $optionText, true);
            }
            $this->assertNotEmpty(array_filter($els, function($e) { return $e->isSelected(); }));
            return;
        }
        $select = new \WebDriverSelect($el);
        $this->assertNodesContain($optionText, $select->getAllSelectedOptions(), 'option');
    }


    public function dontSeeOptionIsSelected($selector, $optionText)
    {
        $el = $this->findField($selector);
        if ($el->getTagName() !== 'select') {
            $els = $this->matchCheckables($selector);
            foreach ($els as $k => $el) {
                $els[$k] = $this->findCheckable($el, $optionText, true);
            }
            $this->assertEmpty(array_filter($els, function($e) { return $e->isSelected(); }));
            return;
        }
        $select = new \WebDriverSelect($el);
        $this->assertNodesNotContain($optionText, $select->getAllSelectedOptions(), 'option');
    }

    public function seeInTitle($title)
    {
        $this->assertContains($title, $this->webDriver->getTitle());
    }

    public function dontSeeInTitle($title)
    {
        $this->assertNotContains($title, $this->webDriver->getTitle());
    }

    /**
     * Accepts JavaScript native popup window created by `window.alert`|`window.confirm`|`window.prompt`.
     * Don't confuse it with modal windows, created by [various libraries](http://jster.net/category/windows-modals-popups).
     *
     */
    public function acceptPopup()
    {
        $this->webDriver->switchTo()->alert()->accept();
    }

    /**
     * Dismisses active JavaScript popup created by `window.alert`|`window.confirm`|`window.prompt`.
     */
    public function cancelPopup()
    {
        $this->webDriver->switchTo()->alert()->dismiss();
    }

    /**
     * Checks that active JavaScript popup created by `window.alert`|`window.confirm`|`window.prompt` contain text.
     *
     * @param $text
     */
    public function seeInPopup($text)
    {
        $this->assertContains($text, $this->webDriver->switchTo()->alert()->getText());
    }

    /**
     * Enters text into native JavaScript prompt popup created by `window.prompt`.
     *
     * @param $keys
     */
    public function typeInPopup($keys)
    {
        $this->webDriver->switchTo()->alert()->sendKeys($keys);
    }

    /**
     * Reloads current page
     */
    public function reloadPage() {
        $this->webDriver->navigate()->refresh();
    }

    /**
     * Moves back in history
     */
    public function moveBack() {
        $this->webDriver->navigate()->back();
        $this->debug($this->_getCurrentUri());
    }

    /**
     * Moves forward in history
     */
    public function moveForward() {
        $this->webDriver->navigate()->forward();
        $this->debug($this->_getCurrentUri());
    }

    /**
     * Submits a form located on page.
     * Specify the form by it's css or xpath selector.
     * Fill the form fields values as array. Hidden fields can't be accessed.
     *
     * This command itself triggers the request to form's action.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * $I->submitForm('#login', array('login' => 'davert', 'password' => '123456'));
     *
     * ```
     *
     * For sample Sign Up form:
     *
     * ``` html
     * <form action="/sign_up">
     *     Login: <input type="text" name="user[login]" /><br/>
     *     Password: <input type="password" name="user[password]" /><br/>
     *     Do you agree to out terms? <input type="checkbox" name="user[agree]" /><br/>
     *     Select pricing plan <select name="plan"><option value="1">Free</option><option value="2" selected="selected">Paid</option></select>
     *     <input type="submit" value="Submit" />
     * </form>
     * ```
     * You can write this:
     *
     * ``` php
     * <?php
     * $I->submitForm('#userForm', array('user' => array('login' => 'Davert', 'password' => '123456', 'agree' => true)));
     *
     * ```
     *
     * @param $selector
     * @param $params
     * @throws \Codeception\Exception\ElementNotFound
     */
    public function submitForm($selector, $params)
    {
        $form = $this->match($this->webDriver,$selector);
        if (empty($form)) throw new ElementNotFound($selector, "Form via CSS or XPath");
        $form = reset($form);
        /** @var $form \WebDriverElement  **/
        foreach ($params as $param => $value) {
            if(!is_array($value) && !is_object($value)){
                $value=(string)$value;
            }
            $els = $form->findElements(\WebDriverBy::name($param));
            $el = reset($els);
            if (empty($el)) throw new ElementNotFound($param);
            if ($el->getTagName() == 'textarea') $this->fillField($el, $value);
            if ($el->getTagName() == 'select') $this->selectOption($el, $value);
            if ($el->getTagName() == 'input') {
                $type = $el->getAttribute('type');
                if ($type == 'text'  or $type == 'password') $this->fillField($el, $value);
                if ($type == 'radio' or $type == 'checkbox') {
                    foreach ($els as $radio) {
                        if ($radio->getAttribute('value') == $value) $this->checkOption($radio);
                    }
                }
            }
        }
        $this->debugSection('Uri', $form->getAttribute('action') ? $form->getAttribute('action') : $this->_getCurrentUri());
        $this->debugSection('Method', $form->getAttribute('method') ? $form->getAttribute('method') : 'GET');
        $this->debugSection('Parameters', json_encode($params));

        $form->submit();
        $this->debugSection('Page', $this->_getCurrentUri());
    }

    /**
     * Waits for element to change or for $timeout seconds to pass. Element "change" is determined
     * by a callback function which is called repeatedly until the return value evaluates to true.
     *
     * ``` php
     * <?php
     * $I->waitForElementChange('#menu', function(\WebDriverElement $el) {
     *     return $el->isDisplayed();
     * }, 100);
     * ?>
     * ```
     *
     * @param $element
     * @param \Closure $callback
     * @param int $timeout seconds
     * @throws \Codeception\Exception\ElementNotFound
     */
    public function waitForElementChange($element, \Closure $callback, $timeout = 30)
    {
        $els = $this->match($this->webDriver, $element);
        if (!count($els)) throw new ElementNotFound($element, "CSS or XPath");
        $el = reset($els);
        $checker = function() use ($el, $callback) {
            return $callback($el);
        };
        $this->webDriver->wait($timeout)->until($checker);
    }

    /**
     * Waits for element to appear on page for $timeout seconds to pass.
     * If element not appears, timeout exception is thrown.
     *
     * ``` php
     * <?php
     * $I->waitForElement('#agree_button', 30); // secs
     * $I->click('#agree_button');
     * ?>
     * ```
     *
     * @param $element
     * @param int $timeout seconds
     * @throws \Exception
     */
    public function waitForElement($element, $timeout = 10)
    {
        $condition = null;
        if (Locator::isID($element)) $condition = \WebDriverExpectedCondition::presenceOfElementLocated(\WebDriverBy::id(substr($element, 1)));
        if (!$condition and Locator::isCSS($element)) $condition = \WebDriverExpectedCondition::presenceOfElementLocated(\WebDriverBy::cssSelector($element));
        if (Locator::isXPath($element)) $condition = \WebDriverExpectedCondition::presenceOfElementLocated(\WebDriverBy::xpath($element));
        if (!$condition) throw new \Exception("Only CSS or XPath allowed");

        $this->webDriver->wait($timeout)->until($condition);
    }
    
    /**
     * Waits for element to be visible on the page for $timeout seconds to pass.
     * If element doesn't appear, timeout exception is thrown.
     *
     * ``` php
     * <?php
     * $I->waitForElementVisible('#agree_button', 30); // secs
     * $I->click('#agree_button');
     * ?>
     * ```
     *
     * @param $element
     * @param int $timeout seconds
     * @throws \Exception
     */
    public function waitForElementVisible($element, $timeout = 10)
    {
        $condition = null;
        if (Locator::isID($element)) $condition = \WebDriverExpectedCondition::visibilityOfElementLocated(\WebDriverBy::id(substr($element, 1)));
        if (!$condition and Locator::isCSS($element)) $condition = \WebDriverExpectedCondition::visibilityOfElementLocated(\WebDriverBy::cssSelector($element));
        if (Locator::isXPath($element)) $condition = \WebDriverExpectedCondition::visibilityOfElementLocated(\WebDriverBy::xpath($element));
        if (!$condition) throw new \Exception("Only CSS or XPath allowed");

        $this->webDriver->wait($timeout)->until($condition);
    }

    /**
     * Waits for element to not be visible on the page for $timeout seconds to pass.
     * If element stays visible, timeout exception is thrown.
     *
     * ``` php
     * <?php
     * $I->waitForElementNotVisible('#agree_button', 30); // secs
     * ?>
     * ```
     *
     * @param $element
     * @param int $timeout seconds
     * @throws \Exception
     */
    public function waitForElementNotVisible($element, $timeout = 10)
    {
        $condition = null;
        if (Locator::isID($element)) $condition = \WebDriverExpectedCondition::invisibilityOfElementLocated(\WebDriverBy::id(substr($element, 1)));
        if (!$condition and Locator::isCSS($element)) $condition = \WebDriverExpectedCondition::invisibilityOfElementLocated(\WebDriverBy::cssSelector($element));
        if (Locator::isXPath($element)) $condition = \WebDriverExpectedCondition::invisibilityOfElementLocated(\WebDriverBy::xpath($element));
        if (!$condition) throw new \Exception("Only CSS or XPath allowed");

        $this->webDriver->wait($timeout)->until($condition);
    }

    /**
     * Waits for text to appear on the page for a specific amount of time.
     * Can also be passed a selector to search in.
     * If text does not appear, timeout exception is thrown.
     *
     * ``` php
     * <?php
     * $I->waitForText('foo', 30); // secs
     * $I->waitForText('foo', 30, '.title'); // secs
     * ?>
     * ```
     *
     * @param string $text
     * @param int $timeout seconds
     * @param null $selector
     * @throws \Exception
     * @internal param string $element
     */
    public function waitForText($text, $timeout = 10, $selector = null)
    {
        $condition = null;
        if (!$selector) {
            $condition = \WebDriverExpectedCondition::textToBePresentInElement(\WebDriverBy::xpath('//body'), $text);
        } else {
            if (Locator::isID($selector)) $condition = \WebDriverExpectedCondition::textToBePresentInElement(\WebDriverBy::id(substr($selector, 1)), $text);
            if (!$condition and Locator::isCSS($selector)) $condition = \WebDriverExpectedCondition::textToBePresentInElement(\WebDriverBy::cssSelector($selector), $text);
            if (Locator::isXPath($selector)) $condition = \WebDriverExpectedCondition::textToBePresentInElement(\WebDriverBy::xpath($selector), $text);
            if (!$condition) throw new \Exception("Only CSS or XPath allowed");
        }
        $this->webDriver->wait($timeout)->until($condition);
    }

    /**
     * Explicit wait.
     *
     * @param $timeout secs
     */
    public function wait($timeout)
    {
        if ($timeout >= 1000) {
            throw new TestRuntime("
                Waiting for more then 1000 seconds: 16.6667 mins\n
                Please note that wait method accepts number of seconds as parameter.");
        }
        sleep($timeout);
    }

    /**
     * Low-level API method.
     * If Codeception commands are not enough, use Selenium WebDriver methods directly
     *
     * ``` php
     * $I->executeInSelenium(function(\WebDriver $webdriver) {
     *   $webdriver->get('http://google.com');
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
        return $function($this->webDriver);
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
     * ``` php
     * <?php
     * $I->executeInSelenium(function (\Webdriver $webdriver) {
     *      $handles=$webdriver->getWindowHandles();
     *      $last_window = end($handles);
     *      $webdriver->switchTo()->window($last_window);
     * });
     * ?>
     * ```
     *
     * @param string|null $name
     */
    public function switchToWindow($name = null) {
        $this->webDriver->switchTo()->window($name);
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
    	if (is_null($name)) {
    		$this->webDriver->switchTo()->defaultContent();
    	} else {
        	$this->webDriver->switchTo()->frame($name);	
    	}
    }

    /**
     * Executes JavaScript and waits for it to return true or for the timeout.
     *
     * In this example we will wait for all jQuery ajax requests are finished or 60 secs otherwise.
     *
     * ``` php
     * <?php
     * $I->waitForJS("return $.active == 0;", 60);
     * ?>
     * ```
     *
     * @param $script
     * @param $timeout int seconds
     */
    public function waitForJS($script, $timeout = 5)
    {
        $condition = function ($wd) use ($script) {
            return $wd->executeScript($script);
        };
        $this->webDriver->wait($timeout)->until($condition);

    }

    /**
     * Executes custom JavaScript
     *
     * @param $script
     * @return mixed
     */
    public function executeJS($script)
    {
        return $this->webDriver->executeScript($script);
    }

    /**
     * Maximizes current window
     */
    public function maximizeWindow()
    {
        $this->webDriver->manage()->window()->maximize();
    }

    /**
     * Performs a simple mouse drag and drop operation.
     *
     * ``` php
     * <?php
     * $I->dragAndDrop('#drag', '#drop');
     * ?>
     * ```
     *
     * @param string $source (CSS ID or XPath)
     * @param string $target (CSS ID or XPath)
     */
    public function dragAndDrop($source, $target)
    {
        $snodes = $this->matchFirstOrFail($this->webDriver, $source);
        $tnodes = $this->matchFirstOrFail($this->webDriver, $target);

        $action = new \WebDriverActions($this->webDriver);
        $action->dragAndDrop($snodes, $tnodes)->perform();
    }

    /**
     * Move mouse over the first element matched by css or xPath on page
     *
     * https://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/moveto
     *
     * @param string $cssOrXPath css or xpath of the web element
     * @param int $offsetX
     * @param int $offsetY
     *
     * @throws \Codeception\Exception\ElementNotFound
     * @return null
     */
    public function moveMouseOver($cssOrXPath, $offsetX = null, $offsetY = null)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $cssOrXPath);
        $this->webDriver->getMouse()->mouseMove($el->getCoordinates(), $offsetX, $offsetY);
    }

    /**
     * Performs contextual click with right mouse button on element matched by CSS or XPath.
     *
     * @param $cssOrXPath
     * @throws \Codeception\Exception\ElementNotFound
     */
    public function clickWithRightButton($cssOrXPath)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $cssOrXPath);
        $this->webDriver->getMouse()->contextClick($el->getCoordinates());
    }

    /**
     * Pauses test execution in debug mode.
     * To proceed test press "ENTER" in console.
     *
     * This method is recommended to use in test development, for additional page analysis, locator searing, etc.
     */
    public function pauseExecution()
    {
        Debug::pause();
    }

    /**
     * Performs a double click on element matched by CSS or XPath.
     *
     * @param $cssOrXPath
     * @throws \Codeception\Exception\ElementNotFound
     */
    public function doubleClick($cssOrXPath)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $cssOrXPath);
        $this->webDriver->getMouse()->doubleClick($el->getCoordinates());
    }

    /**
     * @param $page
     * @param $selector
     * @return array
     */
    protected function match($page, $selector)
    {
        $nodes = array();
        if (Locator::isID($selector)) $nodes = $page->findElements(\WebDriverBy::id(substr($selector, 1)));
        if (!empty($nodes)) return $nodes;
        if (Locator::isCSS($selector)) $nodes = $page->findElements(\WebDriverBy::cssSelector($selector));
        if (!empty($nodes)) return $nodes;
        if (Locator::isXPath($selector)) $nodes = $page->findElements(\WebDriverBy::xpath($selector));
        return $nodes;
    }

    /**
     * @param $page
     * @param $selector
     * @return \RemoteWebElement
     * @throws \Codeception\Exception\ElementNotFound
     */
    protected function matchFirstOrFail($page, $selector)
    {
        $els = $this->match($page, $selector);
        if (count($els)) {
            return reset($els);
        }
        throw new ElementNotFound($selector, 'CSS or XPath');
    }

    /**
     * Presses key on element found by css, xpath is focused
     * A char and modifier (ctrl, alt, shift, meta) can be provided.
     * For special keys use key constants from \WebDriverKeys class.
     *
     * Example:
     *
     * ``` php
     * <?php
     * // <input id="page" value="old" />
     * $I->pressKey('#page','a'); // => olda
     * $I->pressKey('#page',array('ctrl','a'),'new'); //=> new
     * $I->pressKey('#page',array('shift','111'),'1','x'); //=> old!!!1x
     * $I->pressKey('descendant-or-self::*[@id='page']','u'); //=> oldu
     * $I->pressKey('#name', array('ctrl', 'a'), WebDriverKeys::DELETE); //=>''
     * ?>
     * ```
     *
     * @param $element
     * @param $char can be char or array with modifier. You can provide several chars.
     * @throws \Codeception\Exception\ElementNotFound
     */
    public function pressKey($element, $char)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $element);
        $args = func_get_args();
        array_shift($args);
        $keys = array();
        foreach ($args as $key) {
            $keys[] = $this->convertKeyModifier($key);
        }
        $el->sendKeys($keys);
    }

    protected function convertKeyModifier($keys)
    {
        if (!is_array($keys)) return $keys;
        if (!isset($keys[1])) return $keys;
        list($modifier, $key) = $keys;

        switch ($modifier) {
            case 'ctrl':
            case 'control':
                return array(\WebDriverKeys::CONTROL, $key);
            case 'alt':
                return array(\WebDriverKeys::ALT, $key);
            case 'shift':
                return array(\WebDriverKeys::SHIFT, $key);
            case 'meta':
                return array(\WebDriverKeys::META, $key);
        }
        return $keys;
    }

    protected function assertNodesContain($text, $nodes, $selector = null)
    {
        $this->assertThat($nodes, new WebDriverConstraint($text, $this->_getCurrentUri()), $selector);
    }

    protected function assertNodesNotContain($text, $nodes, $selector = null)
    {
        $this->assertThat($nodes, new WebDriverConstraintNot($text, $this->_getCurrentUri()), $selector);
    }

    protected function assertPageContains($needle, $message = '')
    {
        $this->assertThat($this->webDriver->getPageSource(), new PageConstraint($needle, $this->_getCurrentUri()),$message);
    }

    protected function assertPageNotContains($needle, $message = '')
    {
        $this->assertThatItsNot($this->webDriver->getPageSource(), new PageConstraint($needle, $this->_getCurrentUri()),$message);
    }

    /**
     * Append text to an element
     * Can add another selection to a select box
     *
     * ``` php
     * <?php
     * $I->appendField('#mySelectbox', 'SelectValue');
     * $I->appendField('#myTextField', 'appended');
     * ?>
     * ```
     *
     * @param string $field
     * @param string $value
     */
    public function appendField($field, $value)
    {
        $el = $this->findField($field);

        switch($el->getTagName()) {

             //Multiple select
            case "select":
                $matched = false;
                $wdSelect = new \WebDriverSelect($el);
                try {
                    $wdSelect->selectByVisibleText($value);
                    $matched = true;
                } catch (\NoSuchElementWebDriverError $e) {}

                 try {
                    $wdSelect->selectByValue($value);
                    $matched = true;
                } catch (\NoSuchElementWebDriverError $e) {}
                if ($matched) return;

                throw new ElementNotFound(json_encode($value), "Option inside $field matched by name or value");
                break;
            case "textarea":
                    $el->sendKeys($value);
                    return;
                break;
            //Text, Checkbox, Radio
            case "input":
                $type = $el->getAttribute('type');

                if ($type == 'checkbox') {
                    //Find by value or css,id,xpath
                    $field = $this->findCheckable($this->webDriver, $value, true);
                    if (!$field) throw new ElementNotFound($value, "Checkbox or Radio by Label or CSS or XPath");
                    if ($field->isSelected()) return;
                    $field->click();
                    return;
                } elseif ($type == 'radio') {
                    $this->selectOption($field, $value);
                    return;
                } else {
                    $el->sendKeys($value);
                    return;
                }
                break;
            default:
        }

        throw new ElementNotFound($field, "Field by name, label, CSS or XPath");
    }
}
