<?php
namespace Codeception\Module;

use Codeception\Coverage\Subscriber\LocalServer;
use Codeception\Exception\ConnectionException;
use Codeception\Exception\ElementNotFound;
use Codeception\Exception\MalformedLocatorException;
use Codeception\Exception\ModuleConfigException as ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Exception\TestRuntimeException;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Lib\Interfaces\ElementLocator;
use Codeception\Lib\Interfaces\MultiSession as MultiSessionInterface;
use Codeception\Lib\Interfaces\PageSourceSaver;
use Codeception\Lib\Interfaces\Remote as RemoteInterface;
use Codeception\Lib\Interfaces\ScreenshotSaver;
use Codeception\Lib\Interfaces\SessionSnapshot;
use Codeception\Lib\Interfaces\Web as WebInterface;
use Codeception\Module as CodeceptionModule;
use Codeception\PHPUnit\Constraint\Page as PageConstraint;
use Codeception\PHPUnit\Constraint\WebDriver as WebDriverConstraint;
use Codeception\PHPUnit\Constraint\WebDriverNot as WebDriverConstraintNot;
use Codeception\Test\Descriptor;
use Codeception\TestInterface;
use Codeception\Util\Debug;
use Codeception\Util\Locator;
use Codeception\Util\Uri;
use Facebook\WebDriver\Exception\InvalidElementStateException;
use Facebook\WebDriver\Exception\InvalidSelectorException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\UnknownServerException;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\UselessFileDetector;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverSelect;
use GuzzleHttp\Cookie\SetCookie;
use Symfony\Component\DomCrawler\Crawler;

/**
 * New generation Selenium WebDriver module.
 *
 * ## Local Testing
 *
 * ### Selenium
 *
 * 1. Download [Selenium Server](http://docs.seleniumhq.org/download/)
 * 2. Launch the daemon: `java -jar selenium-server-standalone-2.xx.xxx.jar`
 * 3. Configure this module (in acceptance.suite.yml) by setting url and browser:
 *
 * ```yaml
 *     modules:
 *        enabled:
 *           - WebDriver:
 *              url: 'http://localhost/'
 *              browser: firefox
 * ```
 *
 * ### PhantomJS
 *
 * PhantomJS is a headless alternative to Selenium Server that implements
 * [the WebDriver protocol](https://code.google.com/p/selenium/wiki/JsonWireProtocol).
 * It allows you to run Selenium tests on a server without a GUI installed.
 *
 * 1. Download [PhantomJS](http://phantomjs.org/download.html)
 * 2. Run PhantomJS in WebDriver mode: `phantomjs --webdriver=4444`
 * 3. Configure this module (in acceptance.suite.yml) by setting url and `phantomjs` as browser:
 *
 * ```yaml
 *     modules:
 *        enabled:
 *           - WebDriver:
 *              url: 'http://localhost/'
 *              browser: phantomjs
 * ```
 *
 * ## Cloud Testing
 *
 * Cloud Testing services can run your WebDriver tests in the cloud.
 * In case you want to test a local site or site behind a firewall
 * you should use a tunnel application provided by a service.
 *
 * ### SauceLabs
 *
 * 1. Create an account at [SauceLabs.com](http://SauceLabs.com) to get your username and access key
 * 2. In the module configuration use the format `username`:`access_key`@ondemand.saucelabs.com' for `host`
 * 3. Configure `platform` under `capabilities` to define the [Operating System](https://docs.saucelabs.com/reference/platforms-configurator/#/)
 * 4. run a tunnel app if your site can't be accessed from Internet
 *
 * ```yaml
 *     modules:
 *        enabled:
 *           - WebDriver:
 *              url: http://mysite.com
 *              host: '<username>:<access key>@ondemand.saucelabs.com'
 *              port: 80
 *              browser: chrome
 *              capabilities:
 *                  platform: 'Windows 10'
 * ```
 *
 * ### BrowserStack
 *
 * 1. Create an account at [BrowserStack](https://www.browserstack.com/) to get your username and access key
 * 2. In the module configuration use the format `username`:`access_key`@hub.browserstack.com' for `host`
 * 3. Configure `os` and `os_version` under `capabilities` to define the operating System
 * 4. If your site is available only locally or via VPN you should use a tunnel app. In this case add `browserstack.local` capability and set it to true.
 *
 * ```yaml
 *     modules:
 *        enabled:
 *           - WebDriver:
 *              url: http://mysite.com
 *              host: '<username>:<access key>@hub.browserstack.com'
 *              port: 80
 *              browser: chrome
 *              capabilities:
 *                  os: Windows
 *                  os_version: 10
 *                  browserstack.local: true # for local testing
 * ```
 * ### TestingBot
 *
 * 1. Create an account at [TestingBot](https://testingbot.com/) to get your key and secret
 * 2. In the module configuration use the format `key`:`secret`@hub.testingbot.com' for `host`
 * 3. Configure `platform` under `capabilities` to define the [Operating System](https://testingbot.com/support/getting-started/browsers.html)
 * 4. Run [TestingBot Tunnel](https://testingbot.com/support/other/tunnel) if your site can't be accessed from Internet
 *
 * ```yaml
 *     modules:
 *        enabled:
 *           - WebDriver:
 *              url: http://mysite.com
 *              host: '<key>:<secret>@hub.testingbot.com'
 *              port: 80
 *              browser: chrome
 *              capabilities:
 *                  platform: Windows 10
 * ```
 *
 * ## Configuration
 *
 * * `url` *required* - Starting URL for your app.
 * * `browser` *required* - Browser to launch.
 * * `host` - Selenium server host (127.0.0.1 by default).
 * * `port` - Selenium server port (4444 by default).
 * * `restart` - Set to false (default) to share browser session between tests, or set to true to create a separate session for each test.
 * * `window_size` - Initial window size. Set to `maximize` or a dimension in the format `640x480`.
 * * `clear_cookies` - Set to false to keep cookies, or set to true (default) to delete all cookies between tests.
 * * `wait` - Implicit wait (default 0 seconds).
 * * `capabilities` - Sets Selenium2 [desired capabilities](https://github.com/SeleniumHQ/selenium/wiki/DesiredCapabilities). Should be a key-value array.
 * * `connection_timeout` - timeout for opening a connection to remote selenium server (30 seconds by default).
 * * `request_timeout` - timeout for a request to return something from remote selenium server (30 seconds by default).
 * * `http_proxy` - sets http proxy server url for testing a remote server.
 * * `http_proxy_port` - sets http proxy server port
 * * `debug_log_entries` - how many selenium entries to print with `debugWebDriverLogs` or on fail (15 by default).
 *
 * Example (`acceptance.suite.yml`)
 *
 * ```yaml
 *     modules:
 *        enabled:
 *           - WebDriver:
 *              url: 'http://localhost/'
 *              browser: firefox
 *              window_size: 1024x768
 *              capabilities:
 *                  unexpectedAlertBehaviour: 'accept'
 *                  firefox_profile: '~/firefox-profiles/codeception-profile.zip.b64'
 * ```
 *
 * ### Status
 *
 * Stability: **stable**
 * Based on [facebook php-webdriver](https://github.com/facebook/php-webdriver)
 *
 * ## Usage
 *
 * ### Locating Elements
 *
 * Most methods in this module that operate on a DOM element (e.g. `click`) accept a locator as the first argument,
 * which can be either a string or an array.
 *
 * If the locator is an array, it should have a single element,
 * with the key signifying the locator type (`id`, `name`, `css`, `xpath`, `link`, or `class`)
 * and the value being the locator itself.
 * This is called a "strict" locator.
 * Examples:
 *
 * * `['id' => 'foo']` matches `<div id="foo">`
 * * `['name' => 'foo']` matches `<div name="foo">`
 * * `['css' => 'input[type=input][value=foo]']` matches `<input type="input" value="foo">`
 * * `['xpath' => "//input[@type='submit'][contains(@value, 'foo')]"]` matches `<input type="submit" value="foobar">`
 * * `['link' => 'Click here']` matches `<a href="google.com">Click here</a>`
 * * `['class' => 'foo']` matches `<div class="foo">`
 *
 * Writing good locators can be tricky.
 * The Mozilla team has written an excellent guide titled [Writing reliable locators for Selenium and WebDriver tests](https://blog.mozilla.org/webqa/2013/09/26/writing-reliable-locators-for-selenium-and-webdriver-tests/).
 *
 * If you prefer, you may also pass a string for the locator. This is called a "fuzzy" locator.
 * In this case, Codeception uses a a variety of heuristics (depending on the exact method called) to determine what element you're referring to.
 * For example, here's the heuristic used for the `submitForm` method:
 *
 * 1. Does the locator look like an ID selector (e.g. "#foo")? If so, try to find a form matching that ID.
 * 2. If nothing found, check if locator looks like a CSS selector. If so, run it.
 * 3. If nothing found, check if locator looks like an XPath expression. If so, run it.
 * 4. Throw an `ElementNotFound` exception.
 *
 * Be warned that fuzzy locators can be significantly slower than strict locators.
 * Especially if you use Selenium WebDriver with `wait` (aka implicit wait) option.
 * In the example above if you set `wait` to 5 seconds and use XPath string as fuzzy locator,
 * `submitForm` method will wait for 5 seconds at each step.
 * That means 5 seconds finding the form by ID, another 5 seconds finding by CSS
 * until it finally tries to find the form by XPath).
 * If speed is a concern, it's recommended you stick with explicitly specifying the locator type via the array syntax.
 *
 * ## Public Properties
 *
 * * `webDriver` - instance of `\Facebook\WebDriver\Remote\RemoteWebDriver`. Can be accessed from Helper classes for complex WebDriver interactions.
 *
 * ```php
 * // inside Helper class
 * $this->getModule('WebDriver')->webDriver->getKeyboard()->sendKeys('hello, webdriver');
 * ```
 *
 */
class WebDriver extends CodeceptionModule implements
    WebInterface,
    RemoteInterface,
    MultiSessionInterface,
    SessionSnapshot,
    ScreenshotSaver,
    PageSourceSaver,
    ElementLocator,
    ConflictsWithModule
{
    protected $requiredFields = ['browser', 'url'];
    protected $config = [
        'host'               => '127.0.0.1',
        'port'               => '4444',
        'restart'            => false,
        'wait'               => 0,
        'clear_cookies'      => true,
        'window_size'        => false,
        'capabilities'       => [],
        'connection_timeout' => null,
        'request_timeout'    => null,
        'http_proxy'         => null,
        'http_proxy_port'    => null,
        'ssl_proxy'          => null,
        'ssl_proxy_port'     => null,
        'debug_log_entries'  => 15,
    ];

    protected $wd_host;
    protected $capabilities;
    protected $connectionTimeoutInMs;
    protected $requestTimeoutInMs;
    protected $test;
    protected $sessionSnapshots = [];
    protected $sessions = [];
    protected $httpProxy;
    protected $httpProxyPort;
    protected $sslProxy;
    protected $sslProxyPort;

    /**
     * @var RemoteWebDriver
     */
    public $webDriver;

    public function _initialize()
    {
        $this->wd_host = sprintf('http://%s:%s/wd/hub', $this->config['host'], $this->config['port']);
        $this->capabilities = $this->config['capabilities'];
        $this->capabilities[WebDriverCapabilityType::BROWSER_NAME] = $this->config['browser'];
        if ($proxy = $this->getProxy()) {
            $this->capabilities[WebDriverCapabilityType::PROXY] = $proxy;
        }
        $this->connectionTimeoutInMs = $this->config['connection_timeout'] * 1000;
        $this->requestTimeoutInMs = $this->config['request_timeout'] * 1000;
        $this->loadFirefoxProfile();
        try {
            $this->webDriver = RemoteWebDriver::create(
                $this->wd_host,
                $this->capabilities,
                $this->connectionTimeoutInMs,
                $this->requestTimeoutInMs,
                $this->httpProxy,
                $this->httpProxyPort
            );
            $this->sessions[] = $this->_backupSession();
        } catch (WebDriverCurlException $e) {
            throw new ConnectionException(
                $e->getMessage() . "\n \nPlease make sure that Selenium Server or PhantomJS is running."
            );
        }
        $this->webDriver->manage()->timeouts()->implicitlyWait($this->config['wait']);
        $this->initialWindowSize();
    }

    public function _conflicts()
    {
        return 'Codeception\Lib\Interfaces\Web';
    }

    public function _before(TestInterface $test)
    {
        if (!isset($this->webDriver)) {
            $this->_initialize();
        }
        $test->getMetadata()->setCurrent([
            'browser' => $this->config['browser'],
            'capabilities' => $this->config['capabilities']
        ]);
    }

    protected function loadFirefoxProfile()
    {
        if (!array_key_exists('firefox_profile', $this->config['capabilities'])) {
            return;
        }

        $firefox_profile = $this->config['capabilities']['firefox_profile'];
        if (file_exists($firefox_profile) === false) {
            throw new ModuleConfigException(
                __CLASS__,
                "Firefox profile does not exist under given path " . $firefox_profile
            );
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

    public function _after(TestInterface $test)
    {
        if ($this->config['restart']) {
            $this->cleanWebDriver();
            return;
        }
        if ($this->config['clear_cookies'] && isset($this->webDriver)) {
            $this->webDriver->manage()->deleteAllCookies();
        }
    }

    public function _failed(TestInterface $test, $fail)
    {
        $this->debugWebDriverLogs();
        $filename = preg_replace('~\W~', '.', Descriptor::getTestSignature($test));
        $outputDir = codecept_output_dir();
        $this->_saveScreenshot($outputDir . mb_strcut($filename, 0, 245, 'utf-8') . '.fail.png');
        $this->_savePageSource($outputDir . mb_strcut($filename, 0, 244, 'utf-8') . '.fail.html');
        $this->debug("Screenshot and page source were saved into '$outputDir' dir");
    }

    /**
     * Print out latest Selenium Logs in debug mode
     */
    public function debugWebDriverLogs()
    {
        try {
            // Dump out latest Selenium logs
            $logs = $this->webDriver->manage()->getAvailableLogTypes();
            foreach ($logs as $logType) {
                $logEntries = array_slice(
                    $this->webDriver->manage()->getLog($logType),
                    -$this->config['debug_log_entries']
                );
                if (empty($logEntries)) {
                    $this->debugSection("Selenium {$logType} Logs", " EMPTY ");
                    continue;
                }
                $this->debugSection("Selenium {$logType} Logs", "\n" . $this->formatLogEntries($logEntries));
            }
        } catch (UnknownServerException $e) {
            // This only happens with the IE driver, which doesn't support retrieving logs yet:
            // https://github.com/SeleniumHQ/selenium/issues/468
            $this->debug("Unable to retrieve Selenium logs");
        }
    }

    /**
     * Turns an array of log entries into a human-readable string.
     * Each log entry is an array with the keys "timestamp", "level", and "message".
     * See https://code.google.com/p/selenium/wiki/JsonWireProtocol#Log_Entry_JSON_Object
     *
     * @param array $logEntries
     * @return string
     */
    protected function formatLogEntries(array $logEntries)
    {
        $formattedLogs = '';

        foreach ($logEntries as $logEntry) {
            // Timestamp is in milliseconds, but date() requires seconds.
            $time = date('H:i:s', $logEntry['timestamp'] / 1000) .
                // Append the milliseconds to the end of the time string
                '.' . ($logEntry['timestamp'] % 1000);
            $formattedLogs .= "{$time} {$logEntry['level']} - {$logEntry['message']}\n";
        }
        return $formattedLogs;
    }

    public function _afterSuite()
    {
        // this is just to make sure webDriver is cleared after suite
        $this->cleanWebDriver();
    }

    protected function cleanWebDriver()
    {
        foreach ($this->sessions as $session) {
            $this->_loadSession($session);
            try {
                $this->webDriver->quit();
            } catch (UnknownServerException $e) {
                // Session already closed so nothing to do
            }
            unset($this->webDriver);
        }
        $this->sessions = [];
    }

    public function amOnSubdomain($subdomain)
    {
        $url = $this->config['url'];
        $url = preg_replace('~(https?:\/\/)(.*\.)(.*\.)~', "$1$3", $url); // removing current subdomain
        $url = preg_replace('~(https?:\/\/)(.*)~', "$1$subdomain.$2", $url); // inserting new
        $this->_reconfigure(['url' => $url]);
    }

    /**
     * Returns URL of a host.
     * @api
     * @return mixed
     * @throws ModuleConfigException
     */
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

    protected function getProxy()
    {
        $proxyConfig = [];
        if ($this->config['http_proxy']) {
            $proxyConfig['httpProxy'] = $this->config['http_proxy'];
            if ($this->config['http_proxy_port']) {
                $proxyConfig['httpProxy'] .= ':' . $this->config['http_proxy_port'];
            }
        }
        if ($this->config['ssl_proxy']) {
            $proxyConfig['sslProxy'] = $this->config['ssl_proxy'];
            if ($this->config['ssl_proxy_port']) {
                $proxyConfig['sslProxy'] .= ':' . $this->config['ssl_proxy_port'];
            }
        }
        if (!empty($proxyConfig)) {
            $proxyConfig['proxyType'] = 'manual';
            return $proxyConfig;
        }
        return null;
    }

    /**
     * Uri of currently opened page.
     * @return string
     * @api
     * @throws ModuleException
     */
    public function _getCurrentUri()
    {
        $url = $this->webDriver->getCurrentURL();
        if ($url == 'about:blank') {
            throw new ModuleException($this, "Current url is blank, no page was opened");
        }
        return Uri::retrieveUri($url);
    }

    public function _saveScreenshot($filename)
    {
        if ($this->webDriver !== null) {
            $this->webDriver->takeScreenshot($filename);
        } else {
            codecept_debug('WebDriver::_saveScreenshot method has been called when webDriver is not set');
            codecept_debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }
    }

    public function _findElements($locator)
    {
        return $this->match($this->webDriver, $locator);
    }

    /**
     * Saves HTML source of a page to a file
     * @param $filename
     */
    public function _savePageSource($filename)
    {
        file_put_contents($filename, $this->webDriver->getPageSource());
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
        $this->webDriver->manage()->window()->setSize(new WebDriverDimension($width, $height));
    }

    public function seeCookie($cookie, array $params = [])
    {
        $cookies = $this->filterCookies($this->webDriver->manage()->getCookies(), $params);
        $cookies = array_map(
            function ($c) {
                return $c['name'];
            },
            $cookies
        );
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
        $this->assertContains($cookie, $cookies);
    }

    public function dontSeeCookie($cookie, array $params = [])
    {
        $cookies = $this->filterCookies($this->webDriver->manage()->getCookies(), $params);
        $cookies = array_map(
            function ($c) {
                return $c['name'];
            },
            $cookies
        );
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
        $this->assertNotContains($cookie, $cookies);
    }

    public function setCookie($cookie, $value, array $params = [])
    {
        $params['name'] = $cookie;
        $params['value'] = $value;
        if (isset($params['expires'])) { // PhpBrowser compatibility
            $params['expiry'] = $params['expires'];
        }
        if (!isset($params['domain'])) {
            $urlParts = parse_url($this->config['url']);
            if (isset($urlParts['host'])) {
                $params['domain'] = $urlParts['host'];
            }
        }
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
        foreach (['domain', 'path', 'name'] as $filter) {
            if (!isset($params[$filter])) {
                continue;
            }
            $cookies = array_filter(
                $cookies,
                function ($item) use ($filter, $params) {
                    return $item[$filter] == $params[$filter];
                }
            );
        }
        return $cookies;
    }

    public function amOnUrl($url)
    {
        $host = Uri::retrieveHost($url);
        $this->_reconfigure(['url' => $host]);
        $this->debugSection('Host', $host);
        $this->webDriver->get($url);
    }

    public function amOnPage($page)
    {
        $url = Uri::appendPath($this->config['url'], $page);
        $this->debugSection('GET', $url);
        $this->webDriver->get($url);
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

    public function seeInSource($raw)
    {
        $this->assertPageSourceContains($raw);
    }

    public function dontSeeInSource($raw)
    {
        $this->assertPageSourceNotContains($raw);
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
            $page = $this->matchFirstOrFail($this->webDriver, $context);
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
     * @return WebDriverElement
     */
    protected function findClickable($page, $link)
    {
        if (is_array($link) or ($link instanceof WebDriverBy)) {
            return $this->matchFirstOrFail($page, $link);
        }

        // try to match by CSS or XPath
        try {
            $els = $this->match($page, $link, false);
            if (!empty($els)) {
                return reset($els);
            }
        } catch (MalformedLocatorException $e) {
            //ignore exception, link could still match on of the things below
        }

        $locator = Crawler::xpathLiteral(trim($link));

        // narrow
        $xpath = Locator::combine(
            ".//a[normalize-space(.)=$locator]",
            ".//button[normalize-space(.)=$locator]",
            ".//a/img[normalize-space(@alt)=$locator]/ancestor::a",
            ".//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][normalize-space(@value)=$locator]"
        );

        $els = $page->findElements(WebDriverBy::xpath($xpath));
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

        $els = $page->findElements(WebDriverBy::xpath($xpath));
        if (count($els)) {
            return reset($els);
        }

        return null;
    }

    /**
     * @param $selector
     * @return WebDriverElement[]
     * @throws \Codeception\Exception\ElementNotFound
     */
    protected function findFields($selector)
    {
        if ($selector instanceof WebDriverElement) {
            return [$selector];
        }
        if (is_array($selector) || ($selector instanceof WebDriverBy)) {
            $fields = $this->match($this->webDriver, $selector);

            if (empty($fields)) {
                throw new ElementNotFound($selector);
            }
            return $fields;
        }

        $locator = Crawler::xpathLiteral(trim($selector));
        // by text or label
        $xpath = Locator::combine(
            // @codingStandardsIgnoreStart
            ".//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')][(((./@name = $locator) or ./@id = //label[contains(normalize-space(string(.)), $locator)]/@for) or ./@placeholder = $locator)]",
            ".//label[contains(normalize-space(string(.)), $locator)]//.//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')]"
            // @codingStandardsIgnoreEnd
        );
        $fields = $this->webDriver->findElements(WebDriverBy::xpath($xpath));
        if (!empty($fields)) {
            return $fields;
        }

        // by name
        $xpath = ".//*[self::input | self::textarea | self::select][@name = $locator]";
        $fields = $this->webDriver->findElements(WebDriverBy::xpath($xpath));
        if (!empty($fields)) {
            return $fields;
        }

        // try to match by CSS or XPath
        $fields = $this->match($this->webDriver, $selector, false);
        if (!empty($fields)) {
            return $fields;
        }

        throw new ElementNotFound($selector, "Field by name, label, CSS or XPath");
    }

    /**
     * @param $selector
     * @return WebDriverElement
     * @throws \Codeception\Exception\ElementNotFound
     */
    protected function findField($selector)
    {
        $arr = $this->findFields($selector);
        return reset($arr);
    }

    public function seeLink($text, $url = null)
    {
        $nodes = $this->webDriver->findElements(WebDriverBy::partialLinkText($text));
        if (empty($nodes)) {
            $this->fail("No links containing text '$text' were found in page " . $this->_getCurrentUri());
        }
        if ($url) {
            $nodes = array_filter(
                $nodes,
                function (WebDriverElement $e) use ($url) {
                    return trim($e->getAttribute('href')) == trim($url);
                }
            );
            if (empty($nodes)) {
                $this->fail("No links containing text '$text' and URL '$url' were found in page " . $this->_getCurrentUri());
            }
        }
    }

    public function dontSeeLink($text, $url = null)
    {
        $nodes = $this->webDriver->findElements(WebDriverBy::partialLinkText($text));
        if (!$url) {
            if (!empty($nodes)) {
                $this->fail("Link containing text '$text' was found in page " . $this->_getCurrentUri());
            }
            return;
        }
        $nodes = array_filter(
            $nodes,
            function (WebDriverElement $e) use ($url) {
                return trim($e->getAttribute('href')) == trim($url);
            }
        );
        if (!empty($nodes)) {
            $this->fail("Link containing text '$text' and URL '$url' was found in page " . $this->_getCurrentUri());
        }
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
        $this->assertRegExp($uri, $this->_getCurrentUri());
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
        $this->assertNotRegExp($uri, $this->_getCurrentUri());
    }

    public function grabFromCurrentUrl($uri = null)
    {
        if (!$uri) {
            return $this->_getCurrentUri();
        }
        $matches = [];
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
            $els = $form->findElements(WebDriverBy::name($name));
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
            $elements = $el->findElements(WebDriverBy::xpath('.//option[@selected]'));
            if (empty($value) && empty($elements)) {
                return ['True', true];
            }
        }

        $currentValues = [];
        if (is_bool($value)) {
            $currentValues = [false];
        }
        foreach ($elements as $el) {
            switch ($el->getTagName()) {
                case 'input':
                    if ($el->getAttribute('type') === 'radio' || $el->getAttribute('type') === 'checkbox') {
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
                    break;

                case 'textarea':
                    // we include trimmed and real value of textarea for check
                    $currentValues[] = $el->getText(); // trimmed value
                default:
                    $currentValues[] = $el->getAttribute('value'); // raw value
                    break;
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

        $wdSelect = new WebDriverSelect($el);
        if ($wdSelect->isMultiple()) {
            $wdSelect->deselectAll();
        }
        if (!is_array($option)) {
            $option = [$option];
        }

        $matched = false;

        if (key($option) !== 'value') {
            foreach ($option as $opt) {
                try {
                    $wdSelect->selectByVisibleText($opt);
                    $matched = true;
                } catch (NoSuchElementException $e) {
                }
            }
        }

        if ($matched) {
            return;
        }

        if (key($option) !== 'text') {
            foreach ($option as $opt) {
                try {
                    $wdSelect->selectByValue($opt);
                    $matched = true;
                } catch (NoSuchElementException $e) {
                }
            }
        }

        if ($matched) {
            return;
        }

        // partially matching
        foreach ($option as $opt) {
            try {
                $optElement = $el->findElement(WebDriverBy::xpath('.//option [contains (., "' . $opt . '")]'));
                $matched = true;
                if (!$optElement->isSelected()) {
                    $optElement->click();
                }
            } catch (NoSuchElementException $e) {
                // exception treated at the end
            }
        }
        if ($matched) {
            return;
        }
        throw new ElementNotFound(json_encode($option), "Option inside $select matched by name or value");
    }

    public function _initializeSession()
    {
        $this->webDriver = RemoteWebDriver::create($this->wd_host, $this->capabilities);
        $this->sessions[] = $this->_backupSession();
        $this->webDriver->manage()->timeouts()->implicitlyWait($this->config['wait']);
    }

    public function _loadSession($session)
    {
        $this->webDriver = $session;
    }

    public function _backupSession()
    {
        return $this->webDriver;
    }

    public function _closeSession($webDriver)
    {
        $keys = array_keys($this->sessions, $webDriver, true);
        $key = array_shift($keys);
        try {
            $webDriver->quit();
        } catch (UnknownServerException $e) {
            // Session already closed so nothing to do
        }
        unset($this->sessions[$key]);
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

        $wdSelect = new WebDriverSelect($el);

        if (!is_array($option)) {
            $option = [$option];
        }

        $matched = false;

        foreach ($option as $opt) {
            try {
                $wdSelect->deselectByVisibleText($opt);
                $matched = true;
            } catch (NoSuchElementException $e) {
                // exception treated at the end
            }

            try {
                $wdSelect->deselectByValue($opt);
                $matched = true;
            } catch (NoSuchElementException $e) {
                // exception treated at the end
            }
        }

        if ($matched) {
            return;
        }
        throw new ElementNotFound(json_encode($option), "Option inside $select matched by name or value");
    }

    /**
     * @param $context
     * @param $radioOrCheckbox
     * @param bool $byValue
     * @return mixed|null
     */
    protected function findCheckable($context, $radioOrCheckbox, $byValue = false)
    {
        if ($radioOrCheckbox instanceof WebDriverElement) {
            return $radioOrCheckbox;
        }
        if (is_array($radioOrCheckbox) or ($radioOrCheckbox instanceof WebDriverBy)) {
            return $this->matchFirstOrFail($this->webDriver, $radioOrCheckbox);
        }

        $locator = Crawler::xpathLiteral($radioOrCheckbox);
        if ($context instanceof WebDriverElement && $context->getTagName() === 'input') {
            $contextType = $context->getAttribute('type');
            if (!in_array($contextType, ['checkbox', 'radio'], true)) {
                return null;
            }
            $nameLiteral = Crawler::xPathLiteral($context->getAttribute('name'));
            $typeLiteral = Crawler::xPathLiteral($contextType);
            $inputLocatorFragment = "input[@type = $typeLiteral][@name = $nameLiteral]";
            $xpath = Locator::combine(
                // @codingStandardsIgnoreStart
                "ancestor::form//{$inputLocatorFragment}[(@id = ancestor::form//label[contains(normalize-space(string(.)), $locator)]/@for) or @placeholder = $locator]",
                // @codingStandardsIgnoreEnd
                "ancestor::form//label[contains(normalize-space(string(.)), $locator)]//{$inputLocatorFragment}"
            );
            if ($byValue) {
                $xpath = Locator::combine($xpath, "ancestor::form//{$inputLocatorFragment}[@value = $locator]");
            }
        } else {
            $xpath = Locator::combine(
                // @codingStandardsIgnoreStart
                "//input[@type = 'checkbox' or @type = 'radio'][(@id = //label[contains(normalize-space(string(.)), $locator)]/@for) or @placeholder = $locator]",
                // @codingStandardsIgnoreEnd
                "//label[contains(normalize-space(string(.)), $locator)]//input[@type = 'radio' or @type = 'checkbox']"
            );
            if ($byValue) {
                $xpath = Locator::combine($xpath, "//input[@type = 'checkbox' or @type = 'radio'][@value = $locator]");
            }
        }
        $els = $context->findElements(WebDriverBy::xpath($xpath));
        if (count($els)) {
            return reset($els);
        }
        $els = $context->findElements(WebDriverBy::xpath(str_replace('ancestor::form', '', $xpath)));
        if (count($els)) {
            return reset($els);
        }
        $els = $this->match($context, $radioOrCheckbox);
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
        $filePath = realpath(codecept_data_dir() . $filename);
        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("file not found or not readable: $filePath");
        }
        // in order for remote upload to be enabled
        $el->setFileDetector(new LocalFileDetector());

        // skip file detector for phantomjs
        if ($this->isPhantom()) {
            $el->setFileDetector(new UselessFileDetector());
        }
        $el->sendKeys($filePath);
    }

    /**
     * Grabs all visible text from the current page.
     *
     * @return string
     */
    public function getVisibleText()
    {
        $els = $this->webDriver->findElements(WebDriverBy::cssSelector('body'));
        if (count($els)) {
            return $els[0]->getText();
        }

        return "";
    }

    public function grabTextFrom($cssOrXPathOrRegex)
    {
        $els = $this->match($this->webDriver, $cssOrXPathOrRegex, false);
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
            $select = new WebDriverSelect($el);
            return $select->getFirstSelectedOption()->getAttribute('value');
        }
        return $el->getAttribute('value');
    }

    public function grabMultiple($cssOrXpath, $attribute = null)
    {
        $els = $this->match($this->webDriver, $cssOrXpath);
        return array_map(
            function (WebDriverElement $e) use ($attribute) {
                if ($attribute) {
                    return $e->getAttribute($attribute);
                }
                return $e->getText();
            },
            $els
        );
    }


    protected function filterByAttributes($els, array $attributes)
    {
        foreach ($attributes as $attr => $value) {
            $els = array_filter(
                $els,
                function (WebDriverElement $el) use ($attr, $value) {
                    return $el->getAttribute($attr) == $value;
                }
            );
        }
        return $els;
    }

    public function seeElement($selector, $attributes = [])
    {
        $els = $this->matchVisible($selector);
        $els = $this->filterByAttributes($els, $attributes);
        $this->assertNotEmpty($els);
    }

    public function dontSeeElement($selector, $attributes = [])
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
    public function seeElementInDOM($selector, $attributes = [])
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
    public function dontSeeElementInDOM($selector, $attributes = [])
    {
        $els = $this->match($this->webDriver, $selector);
        $els = $this->filterByAttributes($els, $attributes);
        $this->assertEmpty($els);
    }

    public function seeNumberOfElements($selector, $expected)
    {
        $counted = count($this->matchVisible($selector));
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

    public function seeNumberOfElementsInDOM($selector, $expected)
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
        $select = new WebDriverSelect($el);
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
        $select = new WebDriverSelect($el);
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
     * Don't confuse popups with modal windows,
     * as created by [various libraries](http://jster.net/category/windows-modals-popups).
     */
    public function acceptPopup()
    {
        if ($this->isPhantom()) {
            throw new ModuleException($this, 'PhantomJS does not support working with popups');
        }
        $this->webDriver->switchTo()->alert()->accept();
    }

    /**
     * Dismisses the active JavaScript popup, as created by `window.alert`|`window.confirm`|`window.prompt`.
     */
    public function cancelPopup()
    {
        if ($this->isPhantom()) {
            throw new ModuleException($this, 'PhantomJS does not support working with popups');
        }
        $this->webDriver->switchTo()->alert()->dismiss();
    }

    /**
     * Checks that the active JavaScript popup,
     * as created by `window.alert`|`window.confirm`|`window.prompt`, contains the given string.
     *
     * @param $text
     */
    public function seeInPopup($text)
    {
        if ($this->isPhantom()) {
            throw new ModuleException($this, 'PhantomJS does not support working with popups');
        }
        $this->assertContains($text, $this->webDriver->switchTo()->alert()->getText());
    }

    /**
     * Enters text into a native JavaScript prompt popup, as created by `window.prompt`.
     *
     * @param $keys
     */
    public function typeInPopup($keys)
    {
        if ($this->isPhantom()) {
            throw new ModuleException($this, 'PhantomJS does not support working with popups');
        }
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
     * Submits the given form on the page, optionally with the given form
     * values.  Give the form fields values as an array. Note that hidden fields
     * can't be accessed.
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
     * $I->submitForm('#login', [
     *     'login' => 'davert',
     *     'password' => '123456'
     * ]);
     * // or
     * $I->submitForm('#login', [
     *     'login' => 'davert',
     *     'password' => '123456'
     * ], 'submitButtonName');
     *
     * ```
     *
     * For example, given this sample "Sign Up" form:
     *
     * ``` html
     * <form action="/sign_up">
     *     Login:
     *     <input type="text" name="user[login]" /><br/>
     *     Password:
     *     <input type="password" name="user[password]" /><br/>
     *     Do you agree to our terms?
     *     <input type="checkbox" name="user[agree]" /><br/>
     *     Select pricing plan:
     *     <select name="plan">
     *         <option value="1">Free</option>
     *         <option value="2" selected="selected">Paid</option>
     *     </select>
     *     <input type="submit" name="submitButton" value="Submit" />
     * </form>
     * ```
     *
     * You could write the following to submit it:
     *
     * ``` php
     * <?php
     * $I->submitForm(
     *     '#userForm',
     *     [
     *         'user[login]' => 'Davert',
     *         'user[password]' => '123456',
     *         'user[agree]' => true
     *     ],
     *     'submitButton'
     * );
     * ```
     * Note that "2" will be the submitted value for the "plan" field, as it is
     * the selected option.
     *
     * Also note that this differs from PhpBrowser, in that
     * ```'user' => [ 'login' => 'Davert' ]``` is not supported at the moment.
     * Named array keys *must* be included in the name as above.
     *
     * Pair this with seeInFormFields for quick testing magic.
     *
     * ``` php
     * <?php
     * $form = [
     *      'field1' => 'value',
     *      'field2' => 'another value',
     *      'checkbox1' => true,
     *      // ...
     * ];
     * $I->submitForm('//form[@id=my-form]', $form, 'submitButton');
     * // $I->amOnPage('/path/to/form-page') may be needed
     * $I->seeInFormFields('//form[@id=my-form]', $form);
     * ?>
     * ```
     *
     * Parameter values must be set to arrays for multiple input fields
     * of the same name, or multi-select combo boxes.  For checkboxes,
     * either the string value can be used, or boolean values which will
     * be replaced by the checkbox's value in the DOM.
     *
     * ``` php
     * <?php
     * $I->submitForm('#my-form', [
     *      'field1' => 'value',
     *      'checkbox' => [
     *          'value of first checkbox',
     *          'value of second checkbox,
     *      ],
     *      'otherCheckboxes' => [
     *          true,
     *          false,
     *          false
     *      ],
     *      'multiselect' => [
     *          'first option value',
     *          'second option value'
     *      ]
     * ]);
     * ?>
     * ```
     *
     * Mixing string and boolean values for a checkbox's value is not supported
     * and may produce unexpected results.
     *
     * Field names ending in "[]" must be passed without the trailing square
     * bracket characters, and must contain an array for its value.  This allows
     * submitting multiple values with the same name, consider:
     *
     * ```php
     * $I->submitForm('#my-form', [
     *     'field[]' => 'value',
     *     'field[]' => 'another value', // 'field[]' is already a defined key
     * ]);
     * ```
     *
     * The solution is to pass an array value:
     *
     * ```php
     * // this way both values are submitted
     * $I->submitForm('#my-form', [
     *     'field' => [
     *         'value',
     *         'another value',
     *     ]
     * ]);
     * ```
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

        $fields = $form->findElements(
            WebDriverBy::cssSelector('input:enabled,textarea:enabled,select:enabled,input[type=hidden]')
        );
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
            $els = $form->findElements(WebDriverBy::name($button));
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
     * Element "change" is determined by a callback function which is called repeatedly
     * until the return value evaluates to true.
     *
     * ``` php
     * <?php
     * use \Facebook\WebDriver\WebDriverElement
     * $I->waitForElementChange('#menu', function(WebDriverElement $el) {
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
        $el = $this->matchFirstOrFail($this->webDriver, $element);
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
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($this->getLocator($element));
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
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($this->getLocator($element));
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
        $condition = WebDriverExpectedCondition::invisibilityOfElementLocated($this->getLocator($element));
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
            $condition = WebDriverExpectedCondition::textToBePresentInElement(WebDriverBy::xpath('//body'), $text);
            $this->webDriver->wait($timeout)->until($condition);
            return;
        }

        $condition = WebDriverExpectedCondition::textToBePresentInElement($this->getLocator($selector), $text);
        $this->webDriver->wait($timeout)->until($condition);
    }

    /**
     * Wait for $timeout seconds.
     *
     * @param int $timeout secs
     * @throws \Codeception\Exception\TestRuntimeException
     */
    public function wait($timeout)
    {
        if ($timeout >= 1000) {
            throw new TestRuntimeException(
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
     * $I->executeInSelenium(function(\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
     *   $webdriver->get('http://google.com');
     * });
     * ```
     *
     * This runs in the context of the
     * [RemoteWebDriver class](https://github.com/facebook/php-webdriver/blob/master/lib/remote/RemoteWebDriver.php).
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
     * $I->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) {
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

        $action = new WebDriverActions($this->webDriver);
        $action->dragAndDrop($snodes, $tnodes)->perform();
    }

    /**
     * Move mouse over the first element matched by the given locator.
     * If the second and third parameters are given,
     * then the mouse is moved to an offset of the element's top-left corner.
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
     * This method is useful while writing tests,
     * since it allows you to inspect the current page in the middle of a test case.
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
     * @param bool $throwMalformed
     * @return array
     */
    protected function match($page, $selector, $throwMalformed = true)
    {
        if (is_array($selector)) {
            try {
                return $page->findElements($this->getStrictLocator($selector));
            } catch (InvalidSelectorException $e) {
                throw new MalformedLocatorException(key($selector) . ' => ' . reset($selector), "Strict locator");
            } catch (InvalidElementStateException $e) {
                if ($this->isPhantom() and $e->getResults()['status'] == 12) {
                    throw new MalformedLocatorException(
                        key($selector) . ' => ' . reset($selector),
                        "Strict locator ".$e->getCode()
                    );
                }
            }
        }
        if ($selector instanceof WebDriverBy) {
            try {
                return $page->findElements($selector);
            } catch (InvalidSelectorException $e) {
                throw new MalformedLocatorException(
                    sprintf(
                        "WebDriverBy::%s('%s')",
                        $selector->getMechanism(),
                        $selector->getValue()
                    ),
                    'WebDriver'
                );
            }
        }
        $isValidLocator = false;
        $nodes = [];
        try {
            if (Locator::isID($selector)) {
                $isValidLocator = true;
                $nodes = $page->findElements(WebDriverBy::id(substr($selector, 1)));
            }
            if (empty($nodes) and Locator::isCSS($selector)) {
                $isValidLocator = true;
                $nodes = $page->findElements(WebDriverBy::cssSelector($selector));
            }
            if (empty($nodes) and Locator::isXPath($selector)) {
                $isValidLocator = true;
                $nodes = $page->findElements(WebDriverBy::xpath($selector));
            }
        } catch (InvalidSelectorException $e) {
            throw new MalformedLocatorException($selector);
        }
        if (!$isValidLocator and $throwMalformed) {
            throw new MalformedLocatorException($selector);
        }
        return $nodes;
    }

    /**
     * @param array $by
     * @return WebDriverBy
     */
    protected function getStrictLocator(array $by)
    {
        $type = key($by);
        $locator = $by[$type];
        switch ($type) {
            case 'id':
                return WebDriverBy::id($locator);
            case 'name':
                return WebDriverBy::name($locator);
            case 'css':
                return WebDriverBy::cssSelector($locator);
            case 'xpath':
                return WebDriverBy::xpath($locator);
            case 'link':
                return WebDriverBy::linkText($locator);
            case 'class':
                return WebDriverBy::className($locator);
            default:
                throw new MalformedLocatorException(
                    "$by => $locator",
                    "Strict locator can be either xpath, css, id, link, class, name: "
                );
        }
    }

    /**
     * @param $page
     * @param $selector
     * @return WebDriverElement
     * @throws \Codeception\Exception\ElementNotFound
     */
    protected function matchFirstOrFail($page, $selector)
    {
        $els = $this->match($page, $selector);
        if (!count($els)) {
            throw new ElementNotFound($selector, "CSS or XPath");
        }
        return reset($els);
    }

    /**
     * Presses the given key on the given element.
     * To specify a character and modifier (e.g. ctrl, alt, shift, meta), pass an array for $char with
     * the modifier as the first element and the character as the second.
     * For special keys use key constants from WebDriverKeys class.
     *
     * ``` php
     * <?php
     * // <input id="page" value="old" />
     * $I->pressKey('#page','a'); // => olda
     * $I->pressKey('#page',array('ctrl','a'),'new'); //=> new
     * $I->pressKey('#page',array('shift','111'),'1','x'); //=> old!!!1x
     * $I->pressKey('descendant-or-self::*[@id='page']','u'); //=> oldu
     * $I->pressKey('#name', array('ctrl', 'a'), \Facebook\WebDriver\WebDriverKeys::DELETE); //=>''
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
        $keys = [];
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
                return [WebDriverKeys::CONTROL, $key];
            case 'alt':
                return [WebDriverKeys::ALT, $key];
            case 'shift':
                return [WebDriverKeys::SHIFT, $key];
            case 'meta':
                return [WebDriverKeys::META, $key];
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

    protected function assertPageSourceContains($needle, $message = '')
    {
        $this->assertThat(
            $this->webDriver->getPageSource(),
            new PageConstraint($needle, $this->_getCurrentUri()),
            $message
        );
    }

    protected function assertPageSourceNotContains($needle, $message = '')
    {
        $this->assertThatItsNot(
            $this->webDriver->getPageSource(),
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
                $wdSelect = new WebDriverSelect($el);
                try {
                    $wdSelect->selectByVisibleText($value);
                    $matched = true;
                } catch (NoSuchElementException $e) {
                    // exception treated at the end
                }

                try {
                    $wdSelect->selectByValue($value);
                    $matched = true;
                } catch (NoSuchElementException $e) {
                    // exception treated at the end
                }
                if ($matched) {
                    return;
                }

                throw new ElementNotFound(json_encode($value), "Option inside $field matched by name or value");
            case "textarea":
                $el->sendKeys($value);
                return;
            case "div": //allows for content editable divs
                $el->sendKeys(WebDriverKeys::END);
                $el->sendKeys($value);
                return;
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
        }

        throw new ElementNotFound($field, "Field by name, label, CSS or XPath");
    }

    /**
     * @param $selector
     * @return array
     */
    protected function matchVisible($selector)
    {
        $els = $this->match($this->webDriver, $selector);
        $nodes = array_filter(
            $els,
            function (WebDriverElement $el) {
                return $el->isDisplayed();
            }
        );
        return $nodes;
    }

    /**
     * @param $selector
     * @return WebDriverBy
     * @throws \InvalidArgumentException
     */
    protected function getLocator($selector)
    {
        if ($selector instanceof WebDriverBy) {
            return $selector;
        }
        if (is_array($selector)) {
            return $this->getStrictLocator($selector);
        }
        if (Locator::isID($selector)) {
            return WebDriverBy::id(substr($selector, 1));
        }
        if (Locator::isCSS($selector)) {
            return WebDriverBy::cssSelector($selector);
        }
        if (Locator::isXPath($selector)) {
            return WebDriverBy::xpath($selector);
        }
        throw new \InvalidArgumentException("Only CSS or XPath allowed");
    }

    /**
     * @param string $name
     */
    public function saveSessionSnapshot($name)
    {
        $this->sessionSnapshots[$name] = [];

        foreach ($this->webDriver->manage()->getCookies() as $cookie) {
            if (in_array(trim($cookie['name']), [LocalServer::COVERAGE_COOKIE, LocalServer::COVERAGE_COOKIE])) {
                continue;
            }

            if ($this->cookieDomainMatchesConfigUrl($cookie)) {
                $this->sessionSnapshots[$name][] = $cookie;
            }
        }

        $this->debugSection('Snapshot', "Saved \"$name\" session snapshot");
    }

    /**
     * @param string $name
     * @return bool
     */
    public function loadSessionSnapshot($name)
    {
        if (!isset($this->sessionSnapshots[$name])) {
            return false;
        }
        foreach ($this->sessionSnapshots[$name] as $cookie) {
            $this->webDriver->manage()->addCookie($cookie);
        }
        $this->debugSection('Snapshot', "Restored \"$name\" session snapshot");
        return true;
    }

    /**
     * Check if the cookie domain matches the config URL.
     *
     * @param array $cookie
     * @return bool
     */
    private function cookieDomainMatchesConfigUrl(array $cookie)
    {
        if (!array_key_exists('domain', $cookie)) {
            return true;
        }

        $setCookie = new SetCookie();
        $setCookie->setDomain($cookie['domain']);

        return $setCookie->matchesDomain(parse_url($this->config['url'], PHP_URL_HOST));
    }

    /**
     * @return bool
     */
    protected function isPhantom()
    {
        return strpos($this->config['browser'], 'phantom') === 0;
    }

    /**
     * Move to the middle of the given element matched by the given locator.
     * Extra shift, calculated from the top-left corner of the element,
     * can be set by passing $offsetX and $offsetY parameters.
     *
     * ``` php
     * <?php
     * $I->scrollTo(['css' => '.checkout'], 20, 50);
     * ?>
     * ```
     *
     * @param $selector
     * @param int $offsetX
     * @param int $offsetY
     */
    public function scrollTo($selector, $offsetX = null, $offsetY = null)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $selector);
        $x = $el->getLocation()->getX() + $offsetX;
        $y = $el->getLocation()->getY() + $offsetY;
        $this->webDriver->executeScript("window.scrollTo($x, $y)");
    }
}
