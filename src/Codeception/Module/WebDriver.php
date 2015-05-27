<?php
namespace Codeception\Module;

use Codeception\Exception\ElementNotFound;
use Codeception\Exception\ModuleConfig as ModuleConfigException;
use Codeception\Exception\TestRuntime;
use Codeception\Util\Debug;
use Codeception\Util\Locator;
use Codeception\Lib\Interfaces\MultiSession as MultiSessionInterface;
use Codeception\Lib\Interfaces\Web as WebInterface;
use Codeception\Lib\Interfaces\Remote as RemoteInterface;
use Symfony\Component\DomCrawler\Crawler;
use Codeception\PHPUnit\Constraint\WebDriver as WebDriverConstraint;
use Codeception\PHPUnit\Constraint\WebDriverNot as WebDriverConstraintNot;
use Codeception\PHPUnit\Constraint\Page as PageConstraint;

/**
 * New generation Selenium WebDriver module.
 *
 * ## Selenium Installation
 *
 * 1. Download [Selenium Server](http://docs.seleniumhq.org/download/)
 * 2. Launch the daemon: `java -jar selenium-server-standalone-2.xx.xxx.jar`
 *
 *
 * ## PhantomJS Installation
 *
 * PhantomJS is a headless alternative to Selenium Server that implements [the WebDriver protocol](https://code.google.com/p/selenium/wiki/JsonWireProtocol).
 * It allows you to run Selenium tests on a server without a GUI installed.
 *
 * 1. Download [PhantomJS](http://phantomjs.org/download.html)
 * 2. Run PhantomJS in WebDriver mode: `phantomjs --webdriver=4444`
 *
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: davert.codecept@mailican.com
 * * Based on [facebook php-webdriver](https://github.com/facebook/php-webdriver)
 *
 *
 * ## Configuration
 *
 * * url *required* - Starting URL for your app.
 * * browser *required* - Browser to launch.
 * * host - Selenium server host (127.0.0.1 by default).
 * * port - Selenium server port (4444 by default).
 * * restart - Set to false (default) to share browser session between tests, or set to true to create a separate session for each test.
 * * window_size - Initial window size. Set to `maximize` or a dimension in the format `640x480`.
 * * clear_cookies - Set to false to keep cookies, or set to true (default) to delete all cookies between tests.
 * * wait - Implicit wait (default 0 seconds).
 * * capabilities - Sets Selenium2 [desired capabilities](http://code.google.com/p/selenium/wiki/DesiredCapabilities). Should be a key-value array.
 *
 * ### Example (`acceptance.suite.yml`)
 *
 *     modules:
 *        enabled: [WebDriver]
 *        config:
 *           WebDriver:
 *              url: 'http://localhost/'
 *              browser: firefox
 *              window_size: 1024x768
 *              wait: 10
 *              capabilities:
 *                  unexpectedAlertBehaviour: 'accept'
 *                  firefox_profile: '/Users/paul/Library/Application Support/Firefox/Profiles/codeception-profile.zip.b64' 
 *
 *
 * ## Locating Elements
 * 
 * Most methods in this module that operate on a DOM element (e.g. `click`) accept a locator as the first argument, which can be either a string or an array.
 * 
 * If the locator is an array, it should have a single element, with the key signifying the locator type (`id`, `name`, `css`, `xpath`, `link`, or `class`) and the value being the locator itself. This is called a "strict" locator. Examples:
 * 
 * * `['id' => 'foo']` matches `<div id="foo">`
 * * `['name' => 'foo']` matches `<div name="foo">`
 * * `['css' => 'input[type=input][value=foo]']` matches `<input type="input" value="foo">`
 * * `['xpath' => "//input[@type='submit'][contains(@value, 'foo')]"]` matches `<input type="submit" value="foobar">`
 * * `['link' => 'Click here']` matches `<a href="google.com">Click here</a>`
 * * `['class' => 'foo']` matches `<div class="foo">`
 * 
 * Writing good locators can be tricky. The Mozilla team has written an excellent guide titled [Writing reliable locators for Selenium and WebDriver tests](https://blog.mozilla.org/webqa/2013/09/26/writing-reliable-locators-for-selenium-and-webdriver-tests/).
 * 
 * If you prefer, you may also pass a string for the locator. This is called a "fuzzy" locator. In this case, Codeception uses a a variety of heuristics (depending on the exact method called) to determine what element you're referring to. For example, here's the heuristic used for the `submitForm` method:
 * 
 * 1. Does the locator look like an ID selector (e.g. "#foo")? If so, try to find a form matching that ID.
 * 2. If nothing found, check if locator looks like a CSS selector. If so, run it.
 * 3. If nothing found, check if locator looks like an XPath expression. If so, run it.
 * 4. Throw an `ElementNotFound` exception.
 * 
 * Be warned that fuzzy locators can be significantly slower than strict locators. If speed is a concern, it's recommended you stick with explicitly specifying the locator type via the array syntax.
 *
 * ## Migration Guide (Selenium2 -> WebDriver)
 *
 * * `wait` method accepts seconds instead of milliseconds. All waits use second as parameter.
 *
 *
 * # Methods
 */
class WebDriver extends \Codeception\Module implements WebInterface, RemoteInterface, MultiSessionInterface
{

    protected $requiredFields = array('browser', 'url');
    protected $config = array(
        'host' => '127.0.0.1',
        'port' => '4444',
        'restart' => false,
        'wait' => 0,
        'clear_cookies' => true,
        'window_size' => false,
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
        $this->wd_host = sprintf('http://%s:%s/wd/hub', $this->config['host'], $this->config['port']);
        $this->capabilities = $this->config['capabilities'];
        $this->capabilities[\WebDriverCapabilityType::BROWSER_NAME] = $this->config['browser'];
        $this->loadFirefoxProfile();
        $this->webDriver = \RemoteWebDriver::create($this->wd_host, $this->capabilities);
        $this->webDriver->manage()->timeouts()->implicitlyWait($this->config['wait']);
        $this->initialWindowSize();
    }

    public function _before(\Codeception\TestCase $test)
    {
        if (!isset($this->webDriver)) {
            $this->_initialize();
        }
    }

    protected function loadFirefoxProfile()
    {
        if (!array_key_exists('firefox_profile', $this->config['capabilities'])) {
            return;
        }

        $firefox_profile = $this->config['capabilities']['firefox_profile'];
        if (file_exists($firefox_profile) === false) {
            throw new ModuleConfigException(__CLASS__, "Firefox profile does not exists under given path " . $firefox_profile);
        }
        // Set firefox profile as capability
        $this->capabilities['firefox_profile'] = file_get_contents($firefox_profile);
    }

    protected function initialWindowSize()
    {
        if ($this->config['window_size'] == 'maximize') {
            $this->maximizeWindow();
            return;
        }
        $size = explode('x', $this->config['window_size']);
        if (count($size) == 2) {
            $this->resizeWindow(intval($size[0]), intval($size[1]));
        }
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
        if ($this->config['clear_cookies'] && isset($this->webDriver)) {
            $this->webDriver->manage()->deleteAllCookies();
        }
    }

    public function _failed(\Codeception\TestCase $test, $fail)
    {
        $filename = str_replace(['::', '\\', '/'],['.', '', ''], \Codeception\TestCase::getTestSignature($test)) . '.fail';
        $this->_saveScreenshot(codecept_output_dir().$filename.'.png');
        file_put_contents(codecept_output_dir().$filename.'.html', $this->webDriver->getPageSource());
        $this->debug("Screenshot and HTML snapshot were saved into '_output' dir");
    }

    public function _afterSuite()
    {
        // this is just to make sure webDriver is cleared after suite
        if (isset($this->webDriver)) {
            $this->webDriver->quit();
            unset($this->webDriver);
        }
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
        if (!isset($this->config['url'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "Module connection failure. The URL for client can't bre retrieved"
            );
        }
        return $this->config['url'];
    }

    public function _getCurrentUri()
    {
        $url = $this->webDriver->getCurrentURL();
        $parts = parse_url($url);
        if (!$parts) {
            $this->fail("URL couldn't be parsed");
        }
        $uri = "";
        if (isset($parts['path'])) {
            $uri .= $parts['path'];
        }
        if (isset($parts['query'])) {
            $uri .= "?" . $parts['query'];
        }
        if (isset($parts['fragment'])) {
            $uri .= "#" . $parts['fragment'];
        }
        return $uri;
    }

    public function _saveScreenshot($filename)
    {
        $this->webDriver->takeScreenshot($filename);
    }

    /**
     * Takes a screenshot of the current window and saves it to `tests/_output/debug`.
     *
     * ``` php
     * <?php
     * $I->amOnPage('/user/edit');
     * $I->makeScreenshot('edit_page');
     * // saved to: tests/_output/debug/edit_page.png
     * ?>
     * ```
     *
     * @param $name
     */
    public function makeScreenshot($name)
    {
        $debugDir = codecept_log_dir() . 'debug';
        if (!is_dir($debugDir)) {
            mkdir($debugDir, 0777);
        }
        $screenName = $debugDir . DIRECTORY_SEPARATOR . $name . '.png';
        $this->_saveScreenshot($screenName);
        $this->debug("Screenshot saved to $screenName");
    }

    /**
     * Resize the current window.
     *
     * ``` php
     * <?php
     * $I->resizeWindow(800, 600);
     *
     * ```
     *
     * @param int $width
     * @param int $height
     */
    public function resizeWindow($width, $height)
    {
        $this->webDriver->manage()->window()->setSize(new \WebDriverDimension($width, $height));
    }

    public function seeCookie($cookie, array $params = [])
    {
        $cookies = $this->filterCookies($this->webDriver->manage()->getCookies(), $params);
        $cookies = array_map(function ($c) { return $c['name']; }, $cookies);
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
        $this->assertContains($cookie, $cookies);
    }

    public function dontSeeCookie($cookie, array $params = [])
    {
        $cookies = $this->filterCookies($this->webDriver->manage()->getCookies(), $params);
        $cookies = array_map(function ($c) { return $c['name']; }, $cookies);
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
        $this->assertNotContains($cookie, $cookies);
    }

    public function setCookie($cookie, $value, array $params = [])
    {
        $params['name'] = $cookie;
        $params['value'] = $value;
        $this->webDriver->manage()->addCookie($params);
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
    }

    public function resetCookie($cookie, array $params = [])
    {
        $this->webDriver->manage()->deleteCookieNamed($cookie);
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
    }

    public function grabCookie($cookie, array $params = [])
    {
        $params['name'] = $cookie;
        $cookies = $this->filterCookies($this->webDriver->manage()->getCookies(), $params);
        if (empty($cookies)) {
            return null;
        }
        $cookie = reset($cookies);
        return $cookie['value'];
    }

    protected function filterCookies($cookies, $params = [])
    {
        foreach (['domain' ,'path', 'name'] as $filter) {
            if (!isset($params[$filter])) continue;
            $cookies = array_filter($cookies, function ($item) use ($filter, $params) {
                return $item[$filter] == $params[$filter];
            });
        }
        return $cookies;
    }

    public function amOnUrl($url)
    {
        $urlParts = parse_url($url);
        if (!isset($urlParts['host']) or !isset($urlParts['scheme'])) {
            throw new TestRuntime("Wrong URL passes, host and scheme not set");
        }
        $host = $urlParts['scheme'].'://'.$urlParts['host'];
        $this->_reconfigure(['url' => $host]);
        $this->debugSection('Host', $host);
        $this->webDriver->get($url);
    }

    public function amOnPage($page)
    {
        $build = parse_url($this->config['url']);
        $uriparts = parse_url($page);
        
        if ($build === false) {
            throw new \Codeception\Exception\TestRuntime("URL '{$this->config['url']}' is malformed");
        } elseif ($uriparts === false) {
            throw new \Codeception\Exception\TestRuntime("URI '{$page}' is malformed");
        }
        
        foreach ($uriparts as $part => $value) {
            if ($part === 'path' && !empty($build[$part])) {
                $build[$part] = rtrim($build[$part], '/') . '/' . ltrim($value, '/');
            } else {
                $build[$part] = $value;
            }
        }
        $this->webDriver->get(\GuzzleHttp\Url::buildUrl($build));
    }

    public function see($text, $selector = null)
    {
        if (!$selector) {
            return $this->assertPageContains($text);
        }
        $nodes = $this->matchVisible($selector);
        $this->assertNodesContain($text, $nodes, $selector);
    }

    public function dontSee($text, $selector = null)
    {
        if (!$selector) {
            return $this->assertPageNotContains($text);
        }
        $nodes = $this->matchVisible($selector);
        $this->assertNodesNotContain($text, $nodes, $selector);
    }

    /**
     * Checks that the page source contains the given string.
     *
     * ```php
     * <?php
     * $I->seeInPageSource('<link rel="apple-touch-icon"');
     * ```
     *
     * @param $text
     */
    public function seeInPageSource($text)
    {
        $this->assertThat(
            $this->webDriver->getPageSource(),
            new PageConstraint($text, $this->_getCurrentUri()),
            ''
        );
    }

    /**
     * Checks that the page source doesn't contain the given string.
     *
     * @param $text
     */
    public function dontSeeInPageSource($text)
    {
        $this->assertThatItsNot(
            $this->webDriver->getPageSource(),
            new PageConstraint($text, $this->_getCurrentUri()),
            ''
        );
    }

    public function click($link, $context = null)
    {
        $page = $this->webDriver;
        if ($context) {
            $nodes = $this->match($this->webDriver, $context);
            if (empty($nodes)) {
                throw new ElementNotFound($context, 'CSS or XPath');
            }
            $page = reset($nodes);
        }
        $el = $this->findClickable($page, $link);
        if (!$el) {
            $els = $this->match($page, $link);
            $el = reset($els);
        }
        if (!$el) {
            throw new ElementNotFound($link, 'Link or Button or CSS or XPath');
        }
        $el->click();
    }

    /**
     * @param $page
     * @param $link
     * @return \WebDriverElement
     */
    protected function findClickable($page, $link)
    {
        if (is_array($link) or ($link instanceof \WebDriverBy)) {
            return $this->matchFirstOrFail($page, $link);
        }

        // try to match by CSS or XPath
        $els = $this->match($page, $link);
        if (!empty($els)) {
            return reset($els);
        }

        $locator = Crawler::xpathLiteral(trim($link));

        // narrow
        $xpath = Locator::combine(
            ".//a[normalize-space(.)=$locator]",
            ".//button[normalize-space(.)=$locator]",
            ".//a/img[normalize-space(@alt)=$locator]/ancestor::a",
            ".//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][normalize-space(@value)=$locator]"
        );

        $els = $page->findElements(\WebDriverBy::xpath($xpath));
        if (count($els)) {
            return reset($els);
        }

        // wide
        $xpath = Locator::combine(
            ".//a[./@href][((contains(normalize-space(string(.)), $locator)) or .//img[contains(./@alt, $locator)])]",
            ".//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][contains(./@value, $locator)]",
            ".//input[./@type = 'image'][contains(./@alt, $locator)]",
            ".//button[contains(normalize-space(string(.)), $locator)]",
            ".//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][./@name = $locator]",
            ".//button[./@name = $locator]"
        );

        $els = $page->findElements(\WebDriverBy::xpath($xpath));
        if (count($els)) {
            return reset($els);
        }

        return null;
    }

    /**
     * @param $selector
     * @return \WebDriverElement[]
     * @throws \Codeception\Exception\ElementNotFound
     */
    protected function findFields($selector)
    {
        if ($selector instanceof \WebDriverElement) {
            return [$selector];
        }
        if (is_array($selector) || ($selector instanceof \WebDriverBy)) {
            $fields = $this->match($this->webDriver, $selector);
            if (empty($fields)) {
                throw new ElementNotFound($selector);
            }
            return $fields;
        }

        $locator = Crawler::xpathLiteral(trim($selector));
        // by text or label
        $xpath = Locator::combine(
            ".//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')][(((./@name = $locator) or ./@id = //label[contains(normalize-space(string(.)), $locator)]/@for) or ./@placeholder = $locator)]",
            ".//label[contains(normalize-space(string(.)), $locator)]//.//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')]"
        );
        $fields = $this->webDriver->findElements(\WebDriverBy::xpath($xpath));
        if (!empty($fields)) {
            return $fields;
        }

        // by name
        $xpath = ".//*[self::input | self::textarea | self::select][@name = $locator]";
        $fields = $this->webDriver->findElements(\WebDriverBy::xpath($xpath));
        if (!empty($fields)) {
            return $fields;
        }

        // try to match by CSS or XPath
        $fields = $this->match($this->webDriver, $selector);
        if (!empty($fields)) {
            return $fields;
        }

        throw new ElementNotFound($selector, "Field by name, label, CSS or XPath");
    }
    
    /**
     * @param $selector
     * @return \WebDriverElement
     * @throws \Codeception\Exception\ElementNotFound
     */
    protected function findField($selector)
    {
        $arr = $this->findFields($selector);
        return reset($arr);
    }

    public function seeLink($text, $url = null)
    {
        $nodes = $this->webDriver->findElements(\WebDriverBy::partialLinkText($text));
        if (!$url) {
            return $this->assertNodesContain($text, $nodes, 'a');
        }
        $this->assertNodesContain($text, $nodes, "a[href=$url]");
    }


    public function dontSeeLink($text, $url = null)
    {
        $nodes = $this->webDriver->findElements(\WebDriverBy::partialLinkText($text));
        if (!$url) {
            $this->assertNodesNotContain($text, $nodes, 'a');
            return;
        }
        $nodes = array_filter(
            $nodes,
            function (\WebDriverElement $e) use ($url) {
                return trim($e->getAttribute('href')) == trim($url);
            }
        );
        $this->assertNodesNotContain($text, $nodes, "a[href=$url]");
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
        if (!$res) {
            $this->fail("Couldn't match $uri in " . $this->_getCurrentUri());
        }
        if (!isset($matches[1])) {
            $this->fail("Nothing to grab. A regex parameter required. Ex: '/user/(\\d+)'");
        }
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
        $els = $this->findFields($field);
        $this->assert($this->proceedSeeInField($els, $value));
    }

    public function dontSeeInField($field, $value)
    {
        $els = $this->findFields($field);
        $this->assertNot($this->proceedSeeInField($els, $value));
    }
    
    public function seeInFormFields($formSelector, array $params)
    {
        $this->proceedSeeInFormFields($formSelector, $params, false);
    }
    
    public function dontSeeInFormFields($formSelector, array $params)
    {
        $this->proceedSeeInFormFields($formSelector, $params, true);
    }
    
    protected function proceedSeeInFormFields($formSelector, array $params, $assertNot)
    {
        $form = $this->match($this->webDriver, $formSelector);
        if (empty($form)) {
            throw new ElementNotFound($formSelector, "Form via CSS or XPath");
        }
        $form = reset($form);
        foreach ($params as $name => $values) {
            $els = $form->findElements(\WebDriverBy::name($name));
            if (empty($els)) {
                throw new ElementNotFound($name);
            }
            if (!is_array($values)) {
                $values = [$values];
            }
            foreach ($values as $value) {
                $ret = $this->proceedSeeInField($els, $value);
                if ($assertNot) {
                    $this->assertNot($ret);
                } else {
                    $this->assert($ret);
                }
            }
        }
    }
    
    protected function proceedSeeInField(array $elements, $value)
    {
        $strField = reset($elements)->getAttribute('name');
        if (reset($elements)->getTagName() === 'select') {
            $el = reset($elements);
            $elements = $el->findElements(\WebDriverBy::xpath('.//option[@selected]'));
            if (empty($value) && empty($elements)) {
                return ['True', true];
            }
        }
        
        $currentValues = [];
        if (is_bool($value)) {
            $currentValues = [false];
        }

        foreach ($elements as $el) {
            if ($el->getTagName() === 'textarea') {
                $currentValues[] = $el->getText();
            } elseif ($el->getTagName() === 'input' && $el->getAttribute('type') === 'radio' || $el->getAttribute('type') === 'checkbox') {
                if ($el->getAttribute('checked')) {
                    if (is_bool($value)) {
                        $currentValues = [true];
                        break;
                    } else {
                        $currentValues[] = $el->getAttribute('value');
                    }
                }
            } else {
                $currentValues[] = $el->getAttribute('value');
            }
        }
        
        return [
            'Contains',
            $value,
            $currentValues,
            "Failed testing for '$value' in $strField's value: " . implode(', ', $currentValues)
        ];
    }

    public function selectOption($select, $option)
    {
        $el = $this->findField($select);
        if ($el->getTagName() != 'select') {
            $els = $this->matchCheckables($select);
            $radio = null;
            foreach ($els as $el) {
                $radio = $this->findCheckable($el, $option, true);
                if ($radio) {
                    break;
                }
            }
            if (!$radio) {
                throw new ElementNotFound($select, "Radiobutton with value or name '$option in");
            }
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
            } catch (\NoSuchElementException $e) {
            }
        }
        if ($matched) {
            return;
        }
        foreach ($option as $opt) {
            try {
                $wdSelect->selectByValue($opt);
                $matched = true;
            } catch (\NoSuchElementException $e) {
            }
        }
        if ($matched) {
            return;
        }

        // partially matching
        foreach ($option as $opt) {
            try {
                $optElement = $el->findElement(\WebDriverBy::xpath('//option [contains (., "' . $opt . '")]'));
                $matched = true;
                if (!$optElement->isSelected()) {
                    $optElement->click();
                }
            } catch (\NoSuchElementException $e) {
            }
        }
        if ($matched) {
            return;
        }
        throw new ElementNotFound(json_encode($option), "Option inside $select matched by name or value");
    }

    public function _initializeSession()
    {
        $this->webDriver = \RemoteWebDriver::create($this->wd_host, $this->capabilities);
        $this->webDriver->manage()->timeouts()->implicitlyWait($this->config['wait']);
    }

    public function _loadSessionData($data)
    {
        $this->webDriver = $data;
    }

    public function _backupSessionData()
    {
        return $this->webDriver;
    }

    public function _closeSession($webdriver)
    {
        $webdriver->close();
    }

    /*
     * Unselect an option in the given select box.
     *
     * @param $select
     * @param $option
     */
    public function unselectOption($select, $option)
    {
        $el = $this->findField($select);

        $wdSelect = new \WebDriverSelect($el);

        if (!is_array($option)) {
            $option = array($option);
        }

        $matched = false;

        foreach ($option as $opt) {
            try {
                $wdSelect->deselectByVisibleText($opt);
                $matched = true;
            } catch (\NoSuchElementException $e) {
            }

            try {
                $wdSelect->deselectByValue($opt);
                $matched = true;
            } catch (\NoSuchElementException $e) {
            }

        }

        if ($matched) {
            return;
        }
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
        if ($radio_or_checkbox instanceof \WebDriverElement) {
            return $radio_or_checkbox;
        }
        if (is_array($radio_or_checkbox) or ($radio_or_checkbox instanceof \WebDriverBy)) {
            return $this->matchFirstOrFail($this->webDriver, $radio_or_checkbox);
        }

        $locator = Crawler::xpathLiteral($radio_or_checkbox);
        if ($context instanceof \WebDriverElement && $context->getTagName() === 'input') {
            $contextType = $context->getAttribute('type');
            if (!in_array($contextType, ['checkbox', 'radio'], true)) {
                return null;
            }
            $nameLiteral = Crawler::xPathLiteral($context->getAttribute('name'));
            $typeLiteral = Crawler::xPathLiteral($contextType);
            $inputLocatorFragment = "input[@type = $typeLiteral][@name = $nameLiteral]";
            $xpath = Locator::combine(
                "ancestor::form//{$inputLocatorFragment}[(@id = ancestor::form//label[contains(normalize-space(string(.)), $locator)]/@for) or @placeholder = $locator]",
                "ancestor::form//label[contains(normalize-space(string(.)), $locator)]//{$inputLocatorFragment}"
            );
            if ($byValue) {
                $xpath = Locator::combine($xpath, "ancestor::form//{$inputLocatorFragment}[@value = $locator]");
            }
        } else {
            $xpath = Locator::combine(
                "//input[@type = 'checkbox' or @type = 'radio'][(@id = //label[contains(normalize-space(string(.)), $locator)]/@for) or @placeholder = $locator]",
                "//label[contains(normalize-space(string(.)), $locator)]//input[@type = 'radio' or @type = 'checkbox']"
            );
            if ($byValue) {
                $xpath = Locator::combine($xpath, "//input[@type = 'checkbox' or @type = 'radio'][@value = $locator]");
            }
        }
        $els = $context->findElements(\WebDriverBy::xpath($xpath));
        if (count($els)) {
            return reset($els);
        }
        $els = $context->findElements(\WebDriverBy::xpath(str_replace('ancestor::form', '', $xpath)));
        if (count($els)) {
            return reset($els);
        }
        $els = $this->match($context, $radio_or_checkbox);
        if (count($els)) {
            return reset($els);
        }
        return null;
    }

    protected function matchCheckables($selector)
    {
        $els = $this->match($this->webDriver, $selector);
        if (!count($els)) {
            throw new ElementNotFound($selector, "Element containing radio by CSS or XPath");
        }
        return $els;
    }

    public function checkOption($option)
    {
        $field = $this->findCheckable($this->webDriver, $option);
        if (!$field) {
            throw new ElementNotFound($option, "Checkbox or Radio by Label or CSS or XPath");
        }
        if ($field->isSelected()) {
            return;
        }
        $field->click();
    }

    public function uncheckOption($option)
    {
        $field = $this->findCheckable($this->webDriver, $option);
        if (!$field) {
            throw new ElementNotFound($option, "Checkbox by Label or CSS or XPath");
        }
        if (!$field->isSelected()) {
            return;
        }
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
        $filePath = realpath(\Codeception\Configuration::dataDir() . $filename);
        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("file not found or not readable: $filePath");
        }
        // in order for remote upload to be enabled
        $el->setFileDetector(new \LocalFileDetector);
        $el->sendKeys($filePath);
    }

    /**
     * Grabs all visible text from the current page.
     *
     * @return string
     */
    public function getVisibleText()
    {
        $els = $this->webDriver->findElements(\WebDriverBy::cssSelector('body'));
        if (count($els)) {
            return $els[0]->getText();
        }

        return "";
    }

    public function grabTextFrom($cssOrXPathOrRegex)
    {
        $els = $this->match($this->webDriver, $cssOrXPathOrRegex);
        if (count($els)) {
            return $els[0]->getText();
        }
        if (@preg_match($cssOrXPathOrRegex, $this->webDriver->getPageSource(), $matches)) {
            return $matches[1];
        }
        throw new ElementNotFound($cssOrXPathOrRegex, 'CSS or XPath or Regex');
    }

    public function grabAttributeFrom($cssOrXpath, $attribute)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $cssOrXpath);
        return $el->getAttribute($attribute);
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


    protected function filterByAttributes($els, array $attributes)
    {
        foreach ($attributes as $attr => $value) {
            $els = array_filter(
                $els,
                function (\WebDriverElement $el) use ($attr, $value) {
                    return $el->getAttribute($attr) == $value;
                }
            );
        }
        return $els;
    }

    public function seeElement($selector, $attributes = array())
    {
        $els = $this->matchVisible($selector);
        $els = $this->filterByAttributes($els, $attributes);
        $this->assertNotEmpty($els);
    }

    public function dontSeeElement($selector, $attributes = array())
    {
        $els = $this->matchVisible($selector);
        $els = $this->filterByAttributes($els, $attributes);
        $this->assertEmpty($els);
    }

    /**
     * Checks that the given element exists on the page, even it is invisible.
     *
     * ``` php
     * <?php
     * $I->seeElementInDOM('//form/input[type=hidden]');
     * ?>
     * ```
     *
     * @param $selector
     */
    public function seeElementInDOM($selector, $attributes = array())
    {
        $els = $this->match($this->webDriver, $selector);
        $els = $this->filterByAttributes($els, $attributes);
        $this->assertNotEmpty($els);
    }


    /**
     * Opposite of `seeElementInDOM`.
     *
     * @param $selector
     */
    public function dontSeeElementInDOM($selector, $attributes = array())
    {
        $els = $this->match($this->webDriver, $selector);
        $els = $this->filterByAttributes($els, $attributes);
        $this->assertEmpty($els);
    }

    public function seeNumberOfElements($selector, $expected)
    {
        $counted = count($this->match($this->webDriver, $selector));
        if (is_array($expected)) {
            list($floor, $ceil) = $expected;
            $this->assertTrue(
                $floor <= $counted && $ceil >= $counted,
                'Number of elements counted differs from expected range'
            );
        } else {
            $this->assertEquals(
                $expected,
                $counted,
                'Number of elements counted differs from expected number'
            );
        }
    }

    public function seeOptionIsSelected($selector, $optionText)
    {
        $el = $this->findField($selector);
        if ($el->getTagName() !== 'select') {
            $els = $this->matchCheckables($selector);
            foreach ($els as $k => $el) {
                $els[$k] = $this->findCheckable($el, $optionText, true);
            }
            $this->assertNotEmpty(
                array_filter(
                    $els,
                    function ($e) {
                        return $e && $e->isSelected();
                    }
                )
            );
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
            $this->assertEmpty(
                array_filter(
                    $els,
                    function ($e) {
                        return $e && $e->isSelected();
                    }
                )
            );
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
     * Accepts the active JavaScript native popup window, as created by `window.alert`|`window.confirm`|`window.prompt`.
     * Don't confuse popups with modal windows, as created by [various libraries](http://jster.net/category/windows-modals-popups).
     */
    public function acceptPopup()
    {
        $this->webDriver->switchTo()->alert()->accept();
    }

    /**
     * Dismisses the active JavaScript popup, as created by `window.alert`|`window.confirm`|`window.prompt`.
     */
    public function cancelPopup()
    {
        $this->webDriver->switchTo()->alert()->dismiss();
    }

    /**
     * Checks that the active JavaScript popup, as created by `window.alert`|`window.confirm`|`window.prompt`, contains the given string.
     *
     * @param $text
     */
    public function seeInPopup($text)
    {
        $this->assertContains($text, $this->webDriver->switchTo()->alert()->getText());
    }

    /**
     * Enters text into a native JavaScript prompt popup, as created by `window.prompt`.
     *
     * @param $keys
     */
    public function typeInPopup($keys)
    {
        $this->webDriver->switchTo()->alert()->sendKeys($keys);
    }

    /**
     * Reloads the current page.
     */
    public function reloadPage()
    {
        $this->webDriver->navigate()->refresh();
    }

    /**
     * Moves back in history.
     */
    public function moveBack()
    {
        $this->webDriver->navigate()->back();
        $this->debug($this->_getCurrentUri());
    }

    /**
     * Moves forward in history.
     */
    public function moveForward()
    {
        $this->webDriver->navigate()->forward();
        $this->debug($this->_getCurrentUri());
    }

    protected function getSubmissionFormFieldName($name)
    {
        if (substr($name, -2) === '[]') {
            return substr($name, 0, -2);
        }
        return $name;
    }

    /**
     * Submits the given form on the page, optionally with the given form values.
     * Give the form fields values as an array. Note that hidden fields can't be accessed.
     *
     * Skipped fields will be filled by their values from the page.
     * You don't need to click the 'Submit' button afterwards.
     * This command itself triggers the request to form's action.
     *
     * You can optionally specify what button's value to include
     * in the request with the last parameter as an alternative to
     * explicitly setting its value in the second parameter, as
     * button values are not otherwise included in the request.
     * 
     * Examples:
     *
     * ``` php
     * <?php
     * $I->submitForm('#login', array('login' => 'davert', 'password' => '123456'));
     * // or
     * $I->submitForm('#login', array('login' => 'davert', 'password' => '123456'), 'submitButtonName');
     *
     * ```
     *
     * For example, given this sample "Sign Up" form:
     *
     * ``` html
     * <form action="/sign_up">
     *     Login: <input type="text" name="user[login]" /><br/>
     *     Password: <input type="password" name="user[password]" /><br/>
     *     Do you agree to out terms? <input type="checkbox" name="user[agree]" /><br/>
     *     Select pricing plan <select name="plan"><option value="1">Free</option><option value="2" selected="selected">Paid</option></select>
     *     <input type="submit" name="submitButton" value="Submit" />
     * </form>
     * ```
     *
     * You could write the following to submit it:
     *
     * ``` php
     * <?php
     * $I->submitForm('#userForm', array('user' => array('login' => 'Davert', 'password' => '123456', 'agree' => true)), 'submitButton');
     *
     * ```
     * Note that "2" will be the submitted value for the "plan" field, as it is the selected option.
     * 
     * You can also emulate a JavaScript submission by not specifying any buttons in the third parameter to submitForm.
     * 
     * ```php
     * <?php
     * $I->submitForm('#userForm', array('user' => array('login' => 'Davert', 'password' => '123456', 'agree' => true)));
     * 
     * ```
     *
     * @param $selector
     * @param $params
     * @param $button
     */
    public function submitForm($selector, array $params, $button = null)
    {
        $form = $this->match($this->webDriver, $selector);
        if (empty($form)) {
            throw new ElementNotFound($selector, "Form via CSS or XPath");
        }
        $form = reset($form);
        
        $fields = $form->findElements(\WebDriverBy::cssSelector('input:enabled,textarea:enabled,select:enabled,input[type=hidden]'));
        foreach ($fields as $field) {
            $fieldName = $this->getSubmissionFormFieldName($field->getAttribute('name'));
            if (!isset($params[$fieldName])) {
                continue;
            }
            $value = $params[$fieldName];
            if (is_array($value) && $field->getTagName() !== 'select') {
                if ($field->getAttribute('type') === 'checkbox' || $field->getAttribute('type') === 'radio') {
                    $found = false;
                    foreach ($value as $index => $val) {
                        if (!is_bool($val) && $val === $field->getAttribute('value')) {
                            array_splice($params[$fieldName], $index, 1);
                            $value = $val;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found && !empty($value) && is_bool(reset($value))) {
                        $value = array_pop($params[$fieldName]);
                    }
                } else {
                    $value = array_pop($params[$fieldName]);
                }
            }
            
            if ($field->getAttribute('type') === 'checkbox' || $field->getAttribute('type') === 'radio') {
                if ($value === true || $value === $field->getAttribute('value')) {
                    $this->checkOption($field);
                } else {
                    $this->uncheckOption($field);
                }
            } elseif ($field->getAttribute('type') === 'button' || $field->getAttribute('type') === 'submit') {
                continue;
            } elseif ($field->getTagName() === 'select') {
                $this->selectOption($field, $value);
            } else {
                $this->fillField($field, $value);
            }
        }
        
        $this->debugSection(
            'Uri',
            $form->getAttribute('action') ? $form->getAttribute('action') : $this->_getCurrentUri()
        );
        $this->debugSection('Method', $form->getAttribute('method') ? $form->getAttribute('method') : 'GET');
        $this->debugSection('Parameters', json_encode($params));

        $submitted = false;
        if (!empty($button)) {
            $els = $form->findElements(\WebDriverBy::name($button));
            if (!empty($els)) {
                $el = reset($els);
                $el->click();
                $submitted = true;
            }
        }
        
        if (!$submitted) {
            $form->submit();
        }
        
        $this->debugSection('Page', $this->_getCurrentUri());
    }

    /**
     * Waits up to $timeout seconds for the given element to change.
     * Element "change" is determined by a callback function which is called repeatedly until the return value evaluates to true.
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
        if (!count($els)) {
            throw new ElementNotFound($element, "CSS or XPath");
        }
        $el = reset($els);
        $checker = function () use ($el, $callback) {
            return $callback($el);
        };
        $this->webDriver->wait($timeout)->until($checker);
    }

    /**
     * Waits up to $timeout seconds for an element to appear on the page.
     * If the element doesn't appear, a timeout exception is thrown.
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
        $condition = \WebDriverExpectedCondition::presenceOfElementLocated($this->getLocator($element));
        $this->webDriver->wait($timeout)->until($condition);
    }

    /**
     * Waits up to $timeout seconds for the given element to be visible on the page.
     * If element doesn't appear, a timeout exception is thrown.
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
        $condition = \WebDriverExpectedCondition::visibilityOfElementLocated($this->getLocator($element));
        $this->webDriver->wait($timeout)->until($condition);
    }

    /**
     * Waits up to $timeout seconds for the given element to become invisible.
     * If element stays visible, a timeout exception is thrown.
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
        $condition = \WebDriverExpectedCondition::invisibilityOfElementLocated($this->getLocator($element));
        $this->webDriver->wait($timeout)->until($condition);
    }

    /**
     * Waits up to $timeout seconds for the given string to appear on the page.
     * Can also be passed a selector to search in.
     * If the given text doesn't appear, a timeout exception is thrown.
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
     */
    public function waitForText($text, $timeout = 10, $selector = null)
    {
        if (!$selector) {
            $condition = \WebDriverExpectedCondition::textToBePresentInElement(\WebDriverBy::xpath('//body'), $text);
            $this->webDriver->wait($timeout)->until($condition);
            return;
        }

        $condition = \WebDriverExpectedCondition::textToBePresentInElement($this->getLocator($selector), $text);
        $this->webDriver->wait($timeout)->until($condition);
    }

    /**
     * Wait for $timeout seconds.
     *
     * @param int $timeout secs
     * @throws \Codeception\Exception\TestRuntime
     */
    public function wait($timeout)
    {
        if ($timeout >= 1000) {
            throw new TestRuntime(
                "
                Waiting for more then 1000 seconds: 16.6667 mins\n
                Please note that wait method accepts number of seconds as parameter."
            );
        }
        sleep($timeout);
    }

    /**
     * Low-level API method.
     * If Codeception commands are not enough, this allows you to use Selenium WebDriver methods directly:
     *
     * ``` php
     * $I->executeInSelenium(function(\WebDriver $webdriver) {
     *   $webdriver->get('http://google.com');
     * });
     * ```
     *
     * This runs in the context of the [RemoteWebDriver class](https://github.com/facebook/php-webdriver/blob/master/lib/remote/RemoteWebDriver.php).
     * Try not to use this command on a regular basis.
     * If Codeception lacks a feature you need, please implement it and submit a patch.
     *
     * @param callable $function
     */
    public function executeInSelenium(\Closure $function)
    {
        return $function($this->webDriver);
    }

    /**
     * Switch to another window identified by name.
     *
     * The window can only be identified by name. If the $name parameter is blank, the parent window will be used.
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
     * If the window has no name, the only way to access it is via the `executeInSelenium()` method, like so:
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
    public function switchToWindow($name = null)
    {
        $this->webDriver->switchTo()->window($name);
    }

    /**
     * Switch to another frame on the page.
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
    public function switchToIFrame($name = null)
    {
        if (is_null($name)) {
            $this->webDriver->switchTo()->defaultContent();
        } else {
            $this->webDriver->switchTo()->frame($name);
        }
    }

    /**
     * Executes JavaScript and waits up to $timeout seconds for it to return true.
     *
     * In this example we will wait up to 60 seconds for all jQuery AJAX requests to finish.
     *
     * ``` php
     * <?php
     * $I->waitForJS("return $.active == 0;", 60);
     * ?>
     * ```
     *
     * @param string $script
     * @param int $timeout seconds
     */
    public function waitForJS($script, $timeout = 5)
    {
        $condition = function ($wd) use ($script) {
            return $wd->executeScript($script);
        };
        $this->webDriver->wait($timeout)->until($condition);

    }

    /**
     * Executes custom JavaScript.
     *
     * This example uses jQuery to get a value and assigns that value to a PHP variable:
     *
     * ```php
     * <?php
     * $myVar = $I->executeJS('return $("#myField").val()');
     * ?>
     * ```
     *
     * @param $script
     * @return mixed
     */
    public function executeJS($script)
    {
        return $this->webDriver->executeScript($script);
    }

    /**
     * Maximizes the current window.
     */
    public function maximizeWindow()
    {
        $this->webDriver->manage()->window()->maximize();
    }

    /**
     * Performs a simple mouse drag-and-drop operation.
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
     * Move mouse over the first element matched by the given locator.
     * If the second and third parameters are given, then the mouse is moved to an offset of the element's top-left corner.
     * Otherwise, the mouse is moved to the center of the element.
     *
     * ``` php
     * <?php
     * $I->moveMouseOver(['css' => '.checkout'], 20, 50);
     * ?>
     * ```
     *
     * @param string $cssOrXPath css or xpath of the web element
     * @param int $offsetX
     * @param int $offsetY
     *
     * @throws \Codeception\Exception\ElementNotFound
     */
    public function moveMouseOver($cssOrXPath, $offsetX = null, $offsetY = null)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $cssOrXPath);
        $this->webDriver->getMouse()->mouseMove($el->getCoordinates(), $offsetX, $offsetY);
    }

    /**
     * Performs contextual click with the right mouse button on an element.
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
     * This method is useful while writing tests, since it allows you to inspect the current page in the middle of a test case.
     */
    public function pauseExecution()
    {
        Debug::pause();
    }

    /**
     * Performs a double-click on an element matched by CSS or XPath.
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
        if (is_array($selector)) {
            return $page->findElements($this->getStrictLocator($selector));
        }
        if ($selector instanceof \WebDriverBy) {
            return $page->findElements($selector);
        }

        if (Locator::isID($selector)) {
            $nodes = $page->findElements(\WebDriverBy::id(substr($selector, 1)));
        }
        if (!empty($nodes)) {
            return $nodes;
        }
        if (Locator::isCSS($selector)) {
            $nodes = $page->findElements(\WebDriverBy::cssSelector($selector));
        }
        if (!empty($nodes)) {
            return $nodes;
        }
        if (Locator::isXPath($selector)) {
            $nodes = $page->findElements(\WebDriverBy::xpath($selector));
        } else {
            codecept_debug("XPath `$selector` is malformed!");
        }
        return $nodes;
    }

    /**
     * @param array $by
     * @return \WebDriverBy
     */
    protected function getStrictLocator(array $by)
    {
        $type = key($by);
        $locator = $by[$type];
        switch ($type) {
            case 'id':
                return \WebDriverBy::id($locator);
            case 'name':
                return \WebDriverBy::name($locator);
            case 'css':
                return \WebDriverBy::cssSelector($locator);
            case 'xpath':
                return \WebDriverBy::xpath($locator);
            case 'link':
                return \WebDriverBy::linkText($locator);
            case 'class':
                return \WebDriverBy::className($locator);
            default:
                throw new TestRuntime(
                    "Locator type '$by' is not defined. Use either: xpath, css, id, link, class, name"
                );
        }
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
     * Presses the given key on the given element. 
     * To specify a character and modifier (e.g. ctrl, alt, shift, meta), pass an array for $char with 
     * the modifier as the first element and the character as the second.
     * For special keys use key constants from \WebDriverKeys class.
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
     * @param $char Can be char or array with modifier. You can provide several chars.
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
        if (!is_array($keys)) {
            return $keys;
        }
        if (!isset($keys[1])) {
            return $keys;
        }
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
        $this->assertThat(
            htmlspecialchars_decode($this->getVisibleText()),
            new PageConstraint($needle, $this->_getCurrentUri()),
            $message
        );
    }

    protected function assertPageNotContains($needle, $message = '')
    {
        $this->assertThatItsNot(
            htmlspecialchars_decode($this->getVisibleText()),
            new PageConstraint($needle, $this->_getCurrentUri()),
            $message
        );
    }

    /**
     * Append the given text to the given element.
     * Can also add a selection to a select box.
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
     * @throws \Codeception\Exception\ElementNotFound
     */
    public function appendField($field, $value)
    {
        $el = $this->findField($field);

        switch ($el->getTagName()) {

            //Multiple select
            case "select":
                $matched = false;
                $wdSelect = new \WebDriverSelect($el);
                try {
                    $wdSelect->selectByVisibleText($value);
                    $matched = true;
                } catch (\NoSuchElementException $e) {
                }

                try {
                    $wdSelect->selectByValue($value);
                    $matched = true;
                } catch (\NoSuchElementException $e) {
                }
                if ($matched) {
                    return;
                }

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
                    if (!$field) {
                        throw new ElementNotFound($value, "Checkbox or Radio by Label or CSS or XPath");
                    }
                    if ($field->isSelected()) {
                        return;
                    }
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

    /**
     * @param $selector
     * @return array
     */
    protected function matchVisible($selector)
    {
        $nodes = array_filter(
            $this->match($this->webDriver, $selector),
            function (\WebDriverElement $el) {
                return $el->isDisplayed();
            }
        );
        return $nodes;
    }

    /**
     * @param $selector
     * @return \WebDriverBy
     * @throws \Exception
     */
    protected function getLocator($selector)
    {
        if ($selector instanceof \WebDriverBy) {
            return $selector;
        }
        if (is_array($selector)) {
            return $this->getStrictLocator($selector);
        }
        if (Locator::isID($selector)) {
            return \WebDriverBy::id(substr($selector, 1));
        }
        if (Locator::isCSS($selector)) {
            return \WebDriverBy::cssSelector($selector);
        }
        if (Locator::isXPath($selector)) {
            return \WebDriverBy::xpath($selector);
        }
        throw new \Exception("Only CSS or XPath allowed");
    }
}
