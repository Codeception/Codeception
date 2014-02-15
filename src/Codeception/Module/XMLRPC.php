<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleRequire as ModuleRequireException;
use Codeception\Exception\ModuleConfig as ModuleConfigException;

/**
 * Module for testing XMLRPC WebService.
 *
 * This module can be used either with frameworks or PHPBrowser.
 * It tries to guess the framework is is attached to.
 *
 * Whether framework is used it operates via standard framework modules.
 * Otherwise sends raw HTTP requests to url via PHPBrowser.
 *
 * ## Requirements
 *
 * * Module requires installed php_xmlrpc extension
 *
 * ## Status
 *
 * * Maintainer: **tiger-seo**
 * * Stability: **beta**
 * * Contact: tiger.seo@gmail.com
 *
 * ## Configuration
 *
 * * url *optional* - the url of api
 *
 * ## Public Properties
 *
 * * headers - array of headers going to be sent.
 * * params - array of sent data
 * * response - last response (string)
 *
 * @since 1.1.5
 * @author tiger.seo@gmail.com
 */
class XMLRPC extends \Codeception\Module
{
    protected $config = array('url' => "");

    /**
     * @var \Symfony\Component\BrowserKit\Client
     */
    public $client = null;
    public $is_functional = false;

    public $headers = array();
    public $params = array();
    public $response = "";

    public function _initialize()
    {
        if (!function_exists('xmlrpc_encode_request')) {
            throw new ModuleRequireException(__CLASS__, "XMLRPC module requires installed php_xmlrpc extension");
        }
        parent::_initialize();
    }

    public function _before(\Codeception\TestCase $test)
    {
        if (!$this->client) {
            if (!strpos($this->config['url'], '://')) {
                // not valid url
                foreach ($this->getModules() as $module) {
                    if ($module instanceof \Codeception\Lib\Framework) {
                        $this->client = $module->client;
                        $this->is_functional = true;
                        break;
                    }
                }
            } else {
                if (!$this->hasModule('PhpBrowser')) {
                    throw new ModuleConfigException(__CLASS__, "For XMLRPC testing via HTTP please enable PhpBrowser module");
                }
                $this->client = $this->getModule('PhpBrowser')->client;
            }
            if (!$this->client) {
                throw new ModuleConfigException(__CLASS__, "Client for XMLRPC requests not initialized.\nProvide either PhpBrowser module, or a framework module which shares FrameworkInterface");
            }
        }

        $this->headers = array();
        $this->params = array();
        $this->response = '';

        $this->client->setServerParameters(array());
    }

    /**
     * Sets HTTP header
     *
     * @param string $name
     * @param string $value
     */
    public function haveHttpHeader($name, $value) {
        $this->headers[$name] = $value;
    }

    /**
     * Checks response code.
     *
     * @param $num
     */
    public function seeResponseCodeIs($num) {
        \PHPUnit_Framework_Assert::assertEquals($num, $this->client->getInternalResponse()->getStatus());
    }

    /**
     * Checks weather last response was valid XMLRPC.
     * This is done with xmlrpc_decode function.
     *
     */
    public function seeResponseIsXMLRPC() {
        $result = xmlrpc_decode($this->response);
        \PHPUnit_Framework_Assert::assertNotNull($result, 'Invalid response document returned from XmlRpc server');
    }

    /**
     * Sends a XMLRPC method call to remote XMLRPC-server.
     *
     * @param string $methodName
     * @param array $parameters
     */
    public function sendXMLRPCMethodCall($methodName, $parameters = array()) {
        if (! array_key_exists('Content-Type', $this->headers)) {
            $this->headers['Content-Type'] = 'text/xml';
        }

        foreach ($this->headers as $header => $val) {
            $this->client->setServerParameter("HTTP_$header", $val);
        }

        $url = $this->config['url'];

        if (is_array($parameters)) {
            $parameters = $this->scalarizeArray($parameters);
        }

        $requestBody = xmlrpc_encode_request($methodName, array_values($parameters));

        $this->debugSection('Request', $url . PHP_EOL . $requestBody);
        $this->client->request('POST', $url, array(), array(), array(), $requestBody);

        $this->response = $this->client->getInternalResponse()->getContent();
        $this->debugSection('Response', $this->response);

    }
}
