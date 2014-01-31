<?php

namespace Codeception\Module;

use Codeception\Util\Connector\Goutte;
use Codeception\Util\InnerBrowser;
use Codeception\Util\MultiSessionInterface;
use Codeception\Util\RemoteInterface;
use Guzzle\Http\Client;
use Codeception\Exception\TestRuntime;
use Codeception\TestCase;
use Symfony\Component\BrowserKit\Request;

/**
 * Uses [Mink](http://mink.behat.org) with [Goutte](https://github.com/fabpot/Goutte) and [Guzzle](http://guzzlephp.org/) to interact with your application over CURL.
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
 * * relies on [Mink](http://mink.behat.org) and [Guzzle](http://guzzlephp.org/)
 *
 * *Please review the code of non-stable modules and provide patches if you have issues.*
 *
 * ## Configuration
 *
 * * url *required* - start url of your app
 * * curl - curl options
 *
 * ### Example (`acceptance.suite.yml`)
 *
 *     modules:
 *        enabled: [PhpBrowser]
 *        config:
 *           PhpBrowser:
 *              url: 'http://localhost'
 *              curl:
 *                  CURLOPT_RETURNTRANSFER: true
 *
 * ## Public Properties
 *
 * * session - contains Mink Session
 * * guzzle - contains [Guzzle](http://guzzlephp.org/) client instance: `\Guzzle\Http\Client`
 *
 * All SSL certification checks are disabled by default.
 * To configure CURL options use `curl` config parameter.
 *
 */
class PhpBrowser extends InnerBrowser implements RemoteInterface, MultiSessionInterface {

    protected $requiredFields = array('url');
    protected $config = array('curl' => array());

    protected $curl_defaults = array(
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CERTINFO => false,
    );

    /**
     * @var \Codeception\Util\Connector\Goutte
     */
    public $client;

    /**
     * @var \Guzzle\Http\Client
     */
    public $guzzle;

    public function _before() {
        $this->_initializeSession();
    }

    public function _getUrl()
    {
        return $this->config['url'];
    }

    public function _sendRequest($url)
    {
        $this->client->request('GET',$url);
        return $this->client->getInternalResponse()->getContent();
    }

    public function _setHeader($header, $value)
    {
        $this->client->setHeader($header, $value);
    }

    public function amOnSubdomain($subdomain)
    {
        $url = $this->config['url'];
        $url = preg_replace('~(https?:\/\/)(.*\.)(.*\.)~', "$1$3", $url); // removing current subdomain
        $url = preg_replace('~(https?:\/\/)(.*)~', "$1$subdomain.$2", $url); // inserting new
        $this->_reconfigure(array('url' => $url));
    }

    public function _getResponseCode()
    {
        return $this->getResponseStatusCode();
    }

    public function _initializeSession()
    {
        // build up a Guzzle friendly list of configuration options
        // passed in both from our defaults and the respective
        // yaml configuration file (if applicable)
        $curl_config['curl.options'] = $this->curl_defaults;
        foreach ($this->config['curl'] as $key => $val) {
            if (defined($key)) $curl_config['curl.options'][constant($key)] = $val;
        }

        // Guzzle client requires that we set the ssl.certificate_authority config
        // directive if we wish to disable SSL verification
        if ($curl_config['curl.options'][CURLOPT_SSL_VERIFYPEER] !== true) {
            $curl_config['ssl.certificate_authority'] = false;
        }

        $this->client = new Goutte();
        $this->client->setClient($this->guzzle = new Client('', $curl_config));
        $this->client->setBaseUri($this->config['url']);
    }

    public function _backupSessionData()
    {
        return [
            'client'    => $this->client,
            'guzzle'    => $this->guzzle,
            'crawler'   => $this->crawler
        ];
    }

    public function _loadSessionData($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function _closeSession($data)
    {
        unset($data);
    }
}
