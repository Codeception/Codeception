<?php

namespace Codeception\Module;

use Codeception\Exception\TestRuntimeException;
use Codeception\Lib\Connector\Guzzle;
use Codeception\Lib\InnerBrowser;
use Codeception\Lib\Interfaces\MultiSession;
use Codeception\Lib\Interfaces\Remote;
use Codeception\TestCase;
use GuzzleHttp\Client;

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
 * * .. those and other [Guzzle Request options](http://docs.guzzlephp.org/en/latest/clients.html#request-options)
 *
 *
 * ### Example (`acceptance.suite.yml`)
 *
 *     modules:
 *        enabled: [PhpBrowser]
 *        config:
 *           PhpBrowser:
 *              url: 'http://localhost'
 *              auth: ['admin', '123345']
 *              curl:
 *                  CURLOPT_RETURNTRANSFER: true
 *
 * ## Public Properties
 *
 * * guzzle - contains [Guzzle](http://guzzlephp.org/) client instance: `\GuzzleHttp\Client`
 * * client - Symfony BrowserKit instance.
 *
 * All SSL certification checks are disabled by default.
 * Use Guzzle request options to configure certifications and others.
 *
 */
class PhpBrowser extends InnerBrowser implements Remote, MultiSession
{

    protected $requiredFields = ['url'];
    protected $config = ['verify' => false, 'expect' => false, 'timeout' => 30, 'curl' => [], 'refresh_max_interval' => 10];
    protected $guzzleConfigFields = ['headers', 'auth', 'proxy', 'verify', 'cert', 'query', 'ssl_key', 'proxy', 'expect', 'version', 'cookies', 'timeout', 'connect_timeout'];

    /**
     * @var \Codeception\Lib\Connector\Guzzle
     */
    public $client;

    /**
     * @var \GuzzleHttp\Client
     */
    public $guzzle;

    public function _initialize()
    {
        $defaults = array_intersect_key($this->config, array_flip($this->guzzleConfigFields));
        $defaults['config']['curl'] = $this->config['curl'];

        foreach ($this->config['curl'] as $key => $val) {
            if (defined($key)) {
                $defaults['config']['curl'][constant($key)] = $val;
            }
        }
        $this->guzzle = new Client(['base_url' => $this->config['url'], 'defaults' => $defaults]);
        $this->_initializeSession();
    }

    public function _before(TestCase $test)
    {
        $this->_initializeSession();
    }

    public function _getUrl()
    {
        return $this->config['url'];
    }

    public function setHeader($header, $value)
    {
        $this->client->setHeader($header, $value);
    }

    public function amHttpAuthenticated($username, $password)
    {
        $this->client->setAuth($username, $password);
    }

    public function amOnPage($page)
    {
        parent::amOnPage(ltrim($page, '/'));
    }

    public function amOnUrl($url)
    {
        $urlParts = parse_url($url);
        if (!isset($urlParts['host']) or !isset($urlParts['scheme'])) {
            throw new TestRuntimeException("Wrong URL passes, host and scheme not set");
        }
        $host = $urlParts['scheme'] . '://' . $urlParts['host'];
        if (isset($urlParts['port'])) {
            $host .= ':' . $urlParts['port'];
        }
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
        $this->client = new Guzzle();
        $this->client->setClient($this->guzzle);
        $this->client->setBaseUri($this->config['url']);
        $this->client->setRefreshMaxInterval($this->config['refresh_max_interval']);
    }

    public function _backupSession()
    {
        return [
            'client'  => $this->client,
            'guzzle'  => $this->guzzle,
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
