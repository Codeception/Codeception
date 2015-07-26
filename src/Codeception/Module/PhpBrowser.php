<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleException;
use Codeception\Lib\InnerBrowser;
use Codeception\Lib\Interfaces\MultiSession;
use Codeception\Lib\Interfaces\Remote;
use Codeception\TestCase;
use Codeception\Util\Uri;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Uses [Guzzle](http://guzzlephp.org/) to interact with your application over CURL.
 * Module works over CURL and requires **PHP CURL extension** to be enabled.
 *
 * Use to perform web acceptance tests with non-javascript browser.
 *
 * If test fails stores last shown page in 'output' dir.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: davert.codecept@mailican.com
 * * Works with [Guzzle](http://guzzlephp.org/)
 *
 * *Please review the code of non-stable modules and provide patches if you have issues.*
 *
 * ## Configuration
 *
 * * url *required* - start url of your app
 * * curl - curl options
 * * headers - ...
 * * cookies - ...
 * * auth - ...
 * * verify - ...
 * * .. those and other [Guzzle Request options](http://docs.guzzlephp.org/en/latest/request-options.html)
 *
 *
 * ### Example (`acceptance.suite.yml`)
 *
 *     modules:
 *        enabled:
 *            - PhpBrowser:
 *                url: 'http://localhost'
 *                auth: ['admin', '123345']
 *                curl:
 *                    CURLOPT_RETURNTRANSFER: true
 *
 *
 * All SSL certification checks are disabled by default.
 * Use Guzzle request options to configure certifications and others.
 *
 * ## Public API
 *
 * Those properties and methods are expected to be used in Helper classes:
 *
 * Properties:
 *
 * * `guzzle` - contains [Guzzle](http://guzzlephp.org/) client instance: `\GuzzleHttp\Client`
 * * `client` - Symfony BrowserKit instance.
 *
 */
class PhpBrowser extends InnerBrowser implements Remote, MultiSession
{

    private $isGuzzlePsr7;
    protected $requiredFields = ['url'];

    protected $config = [
        'verify' => false,
        'expect' => false,
        'timeout' => 30,
        'curl' => [],
        'refresh_max_interval' => 10,

        // required defaults (not recommended to change)
        'allow_redirects' => false,
        'http_errors' => false,
        'cookies' => true
    ];

    protected $guzzleConfigFields = [
        'headers',
        'auth',
        'proxy',
        'verify',
        'cert',
        'query',
        'ssl_key',
        'proxy',
        'expect',
        'version',
        'cookies',
        'timeout',
        'connect_timeout'
    ];

    /**
     * @var \Codeception\Lib\Connector\Guzzle6
     */
    public $client;

    /**
     * @var GuzzleClient
     */
    public $guzzle;

    public function _initialize()
    {
        $this->client = $this->guessGuzzleConnector();
        $this->_initializeSession();
    }

    protected function guessGuzzleConnector()
    {
        if (!class_exists('GuzzleHttp\Client')) {
            throw new ModuleException($this, "Guzzle is not installed. Please install `guzzlehttp/guzzle` with composer");
        }
        if (class_exists('GuzzleHttp\Url')) {
            $this->isGuzzlePsr7 = false;
            return new \Codeception\Lib\Connector\Guzzle();
        }
        $this->isGuzzlePsr7 = true;
        return new \Codeception\Lib\Connector\Guzzle6();
    }

    public function _before(TestCase $test)
    {
        if (!$this->client) {
            $this->client = $this->guessGuzzleConnector();
        }
        $this->_initializeSession();
    }

    public function _getUrl()
    {
        return $this->config['url'];
    }

    /**
     * Sets the HTTP header to the passed value - which is used on
     * subsequent HTTP requests through PhpBrowser.
     *
     * Example:
     * ```php
     * <?php
     * $I->setHeader('X-Requested-With', 'Codeception');
     * $I->amOnPage('test-headers.php');
     * ?>
     * ```
     *
     * @param string $name the name of the request header
     * @param string $value the value to set it to for subsequent
     *        requests
     */
    public function setHeader($name, $value)
    {
        $this->client->setHeader($name, $value);
    }

    /**
     * Deletes the header with the passed name.  Subsequent requests
     * will not have the deleted header in its request.
     *
     * Example:
     * ```php
     * <?php
     * $I->setHeader('X-Requested-With', 'Codeception');
     * $I->amOnPage('test-headers.php');
     * // ...
     * $I->deleteHeader('X-Requested-With');
     * $I->amOnPage('some-other-page.php');
     * ?>
     * ```
     * 
     * @param string $name the name of the header to delete.
     */
    public function deleteHeader($name)
    {
        $this->client->deleteHeader($name);
    }

    public function amHttpAuthenticated($username, $password)
    {
        $this->client->setAuth($username, $password);
    }

    public function amOnUrl($url)
    {
        $host = Uri::retrieveHost($url);
        $this->_reconfigure(['url' => $host]);
        $page = substr($url, strlen($host));
        $this->debugSection('Host', $host);
        $this->amOnPage($page);
    }

    public function amOnSubdomain($subdomain)
    {
        $url = $this->config['url'];
        $url = preg_replace('~(https?:\/\/)(.*\.)(.*\.)~', "$1$3", $url); // removing current subdomain
        $url = preg_replace('~(https?:\/\/)(.*)~', "$1$subdomain.$2", $url); // inserting new
        $this->_reconfigure(['url' => $url]);
    }

    protected function onReconfigure()
    {
        $this->_initializeSession();
    }

    /**
     * Low-level API method.
     * If Codeception commands are not enough, use [Guzzle HTTP Client](http://guzzlephp.org/) methods directly
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->executeInGuzzle(function (\GuzzleHttp\Client $client) {
     *      $client->get('/get', ['query' => ['foo' => 'bar']]);
     * });
     * ?>
     * ```
     *
     * It is not recommended to use this command on a regular basis.
     * If Codeception lacks important Guzzle Client methods, implement them and submit patches.
     *
     * @param callable $function
     */
    public function executeInGuzzle(\Closure $function)
    {
        return $function($this->guzzle);
    }


    public function _getResponseCode()
    {
        return $this->getResponseStatusCode();
    }

    public function _initializeSession()
    {
        $defaults = array_intersect_key($this->config, array_flip($this->guzzleConfigFields));
        $defaults['config']['curl'] = $this->config['curl'];

        foreach ($this->config['curl'] as $key => $val) {
            if (defined($key)) {
                $defaults['config']['curl'][constant($key)] = $val;
            }
        }

        if ($this->isGuzzlePsr7) {
            $defaults['base_uri'] = $this->config['url'];
            $this->guzzle = new GuzzleClient($defaults);
        } else {
            $this->guzzle = new GuzzleClient(['base_url' => $this->config['url'], 'defaults' => $defaults]);
            $this->client->setBaseUri($this->config['url']);
        }

        $this->client->setRefreshMaxInterval($this->config['refresh_max_interval']);
        $this->client->setClient($this->guzzle);
    }

    public function _backupSession()
    {
        return [
            'client' => $this->client,
            'guzzle' => $this->guzzle,
            'crawler' => $this->crawler
        ];
    }

    public function _loadSession($session)
    {
        foreach ($session as $key => $val) {
            $this->$key = $val;
        }
    }

    public function _closeSession($session)
    {
        unset($session);
    }
}
