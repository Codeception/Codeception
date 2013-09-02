<?php
namespace Codeception\Module;

use Codeception\Exception\ElementNotFound;
use Codeception\Util\Locator;
use Codeception\Util\WebInterface;
use Symfony\Component\DomCrawler\Crawler;
use Codeception\PHPUnit\Constraint\WebDriver as WebDriverConstraint;
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
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **alpha**
 * * Contact: davert.codecept@mailican.com
 * * Based on [faebook php-webdriver](https://github.com/facebook/php-webdriver)
 *
 * ## Configuration
 *
 * * url *required* - start url for your app
 * * browser *required* - browser that would be launched
 * * host  - Selenium server host (localhost by default)
 * * port - Selenium server port (4444 by default)
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
 *
 * Class WebDriver
 * @package Codeception\Module
 */
class WebDriver extends \Codeception\Module implements WebInterface {

    protected $requiredFields = array('browser', 'url');
    protected $config = array(
        'host' => '127.0.0.1',
        'port' => '4444',
        'restart' => false,
        'wait' => 5,
        'capabilities' => array());
    
    protected $wd_host;
    protected $capabilities;

    /**
     * @var \WebDriver
     */
    public $webDriver;

    public function _initialize()
    {
        $this->wd_host =  sprintf('http://%s:%s/wd/hub', $this->config['host'], $this->config['port']);
        $this->capabilities = $this->config['capabilities'];
        $this->capabilities[\WebDriverCapabilityType::BROWSER_NAME] = $this->config['browser'];
        $this->webDriver = new \RemoteWebDriver($this->wd_host, $this->capabilities);
        $this->webDriver->manage()->timeouts()->implicitlyWait($this->config['wait']);
        $wd = $this->webDriver;
        register_shutdown_function(function () use ($wd) {
            try { $wd->quit(); } catch (\UnhandledWebDriverError $e) {}
        });
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->webDriver->manage()->deleteAllCookies();
        $size = $this->webDriver->manage()->window()->getSize();
        $this->debugSection("Window", $size->getWidth().'x'.$size->getHeight());
    }

    public function _after(\Codeception\TestCase $test)
    {
        $this->config['restart']
            ? $this->webDriver->close()
            : $this->amOnPage('/');
    }

    public function _afterSuite()
    {
        $this->webDriver->quit();
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
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
    }

    public function see($text, $selector = null)
    {
        if (!$selector) return $this->assertPageContains($text);
        $nodes = $this->match($this->webDriver, $selector);
        $this->assertNodesContain($text, $nodes);
    }

    public function dontSee($text, $selector = null)
    {
        if (!$selector) return $this->assertPageNotContains($text);
        $nodes = $this->match($this->webDriver, $selector);
        $this->assertNodesNotContain($text, $nodes);
    }

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
        if (!$url) return $this->assertNodesContain($text, $nodes);
        $nodes = array_filter($nodes, function(\WebDriverElement $e) use ($url) {
                $parts = parse_url($url);
                if (!$parts) $this->fail("Link URL of '$url' couldn't be parsed");
                $uri = "";
                if (isset($parts['path'])) $uri .= $parts['path'];
                if (isset($parts['query'])) $uri .= "?".$parts['query'];
                if (isset($parts['fragment'])) $uri .= "#".$parts['fragment'];
                return $uri == trim($url);
        });
        $this->assertNodesContain($text, $nodes);
    }

    public function dontSeeLink($text, $url = null)
    {
        $nodes = $this->webDriver->findElements(\WebDriverBy::partialLinkText($text));
        if (!$url) return $this->assertNodesNotContain($text, $nodes);
        $nodes = array_filter($nodes, function(\WebDriverElement $e) use ($url) {
            return trim($e->getAttribute('href')) == trim($url);
        });
        $this->assertNodesNotContain($text, $nodes);
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
        if (!$uri) return $this->_getCurrentUri();
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

        $select = new \WebDriverSelect($el);
        if ($select->isMultiple()) $select->deselectAll();
        if (!is_array($option)) $option = array($option);

        $matched = false;

        foreach ($option as $opt) {
            try {
                $select->selectByVisibleText($opt);
                $matched = true;
            } catch (\NoSuchElementWebDriverError $e) {}
        }
        foreach ($option as $opt) {
            try {
            $select->selectByValue($opt);
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

        $select = new \WebDriverSelect($el);

        if (!is_array($option)) $option = array($option);

        $matched = false;

        foreach ($option as $opt) {
            try {
                $select->deselectByVisibleText($opt);
                $matched = true;
            } catch (\NoSuchElementWebDriverError $e) {}

            try {
                $select->deselectByValue($opt);
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
        $el->sendKeys(\Codeception\Configuration::dataDir().$filename);
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
        if ($el->getTagName() == 'textarea') return $el->getText();
        if ($el->getTagName() == 'input') return $el->getAttribute('value');
        if ($el->getTagName() != 'select') return null;
        $select = new \WebDriverSelect($el);
        return $select->getFirstSelectedOption()->getAttribute('value');
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
        $this->assertNodesContain($optionText, $select->getAllSelectedOptions());
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
        $this->assertNodesNotContain($optionText, $select->getAllSelectedOptions());
    }

    public function seeInTitle($title)
    {
        $this->assertContains($title, $this->webDriver->getTitle());
    }

    public function dontSeeInTitle($title)
    {
        $this->assertNotContains($title, $this->webDriver->getTitle());
    }

    public function acceptPopup()
    {
        $this->webDriver->switchTo()->alert()->accept();
    }

    public function cancelPopup()
    {
        $this->webDriver->switchTo()->alert()->dismiss();
    }

    public function seeInPopup($text)
    {
        $this->assertContains($text, $this->webDriver->switchTo()->alert()->getText());
    }

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
            $els = $form->findElements(\WebDriverBy::name($param));
            $el = reset($els);
            if ($el->getTagName() == 'textarea') $this->fillField($el, $value);
            if ($el->getTagName() == 'select') $this->selectOption($el, $value);
            if ($el->getTagName() == 'input') {
                $type = $el->getAttribute('type');
                if ($type == 'text') $this->fillField($el, $value);
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
     * Waits until element has changed according to callback function
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
     * @param int $timeout
     * @throws \Codeception\Exception\ElementNotFound
     */
    public function waitForElementChange($element, \Closure $callback, $timeout = 30)
    {
        $els = $this->match($this->webDriver, $element);
        if (!count($els)) throw new ElementNotFound($element, "CSS or XPath");
        $el = reset($els);
        $checker = function() use ($el, $callback) {
            $callback($el);
        };
        $this->webDriver->wait($timeout)->until($checker);
    }

    /**
     * Waits for element to appear on page for specific amount of time.
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
     * ```
     * <?php
     * $I->executeInSelenium(function (\Webdriver $webdriver) {
     *      $handles=$webDriver->getWindowHandles();
     *      $last_window = end($handles);
     *      $webDriver->switchTo()->window($name);
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
        $this->webDriver->switchTo()->frame($name);
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

    protected function assertNodesContain($text, $nodes)
    {
        $this->assertThat($nodes, new WebDriverConstraint($text, $this->_getCurrentUri()), $text);
    }

    protected function assertNodesNotContain($text, $nodes)
    {
        $this->assertThatItsNot($nodes, new WebDriverConstraint($text, $this->_getCurrentUri()), $text);
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
        $snodes = $this->match($this->webDriver, $source);
        if (empty($snodes)) throw new ElementNotFound($source,'CSS or XPath');
        $snodes = reset($snodes);

        $tnodes = $this->match($this->webDriver, $target);
        if (empty($tnodes)) throw new ElementNotFound($target,'CSS or XPath');
        $tnodes = reset($tnodes);

        $action = new \WebDriverActions($this->webDriver);
        $action->dragAndDrop($snodes, $tnodes)->perform();
    }

}
