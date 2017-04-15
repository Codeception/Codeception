<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleRequire as ModuleRequireException;
use Codeception\Exception\ModuleConfig as ModuleConfigException;

/**
 * Module for testing JSONRPC WebService.
 *
 * This module can be used either with frameworks or PHPBrowser.
 * It tries to guess the framework is is attached to.
 *
 * Whether framework is used it operates via standard framework modules.
 * Otherwise sends raw HTTP requests to url via PHPBrowser.
 *
 * ## Requirements
 *
 * * Module requires installed php_JSONrpc extension
 *
 * ## Status
 *
 * * Maintainer: **stenly.kurinec**
 * * Stability: **beta**
 * * Contact: stenly.kurinec@gmail.com
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
 * @since 1.6.4.2
 * @author stenly.kurinec@gmail.com
 */
class JSONRPC extends \Codeception\Module
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
        parent::_initialize();
    }

    public function _before(\Codeception\TestCase $test)
    {
        if (!$this->client) {
            if (!strpos($this->config['url'], '://')) {
                // not valid url
                foreach ($this->getModules() as $module) {
                    if ($module instanceof \Codeception\Util\Framework) {
                        $this->client = $module->client;
                        $this->is_functional = true;
                        break;
                    }
                }
            } else {
                if (!$this->hasModule('PhpBrowser')) {
                    throw new ModuleConfigException(__CLASS__, "For JSONRPC testing via HTTP please enable PhpBrowser module");
                }
                $this->client = $this->getModule('PhpBrowser')->session->getDriver()->getClient();
            }
            if (!$this->client) {
                throw new ModuleConfigException(__CLASS__, "Client for JSONRPC requests not initialized.\nProvide either PhpBrowser module, or a framework module which shares FrameworkInterface");
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
        \PHPUnit_Framework_Assert::assertEquals($num, $this->client->getResponse()->getStatus());
    }

    /**
     * Checks weather last response was valid JSONRPC.
     * This is done with JSONrpc_decode function.
     *
     */
    public function seeResponseIsJSONRPC() {
        $result = json_decode($this->response);
        \PHPUnit_Framework_Assert::assertNotNull($result, 'Invalid response document returned from JSONRpc server');
    }

    /**
     * Sends a JSONRPC method call to remote JSONRPC-server.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // response: {name: john, email: john@gmail.com}
     * $I->sendJSONRPCMethodCall('user.login', array('email' => 'john@gmail.com', 'pass' => '123456'));
     *
     * // response {user: john, profile: { email: john@gmail.com }}
     * $I->seeResponseContainsJson(array('email' => 'john@gmail.com'));
     *
     * ?>
     * ```
     *
     * @param string $methodName
     * @param array $parameters
     * @param int $id
     */
    public function sendJSONRPCMethodCall($methodName, $parameters = array(), $id=null) {
        if (! array_key_exists('Content-Type', $this->headers)) {
            $this->headers['Content-Type'] = 'application/json';
        }

        foreach ($this->headers as $header => $val) {
            $this->client->setServerParameter("HTTP_$header", $val);
        }

        $url = $this->config['url'];

        $payload = array("jsonrpc"=>"2.0", "method"=>$methodName, "params"=>$parameters);
        if(!is_null($id)) {
            $payload['id'] = $id;
        }

        $requestBody = json_encode($payload);

        $this->debugSection('Request', $url . PHP_EOL . $requestBody);
        $this->client->request('POST', $url, array(), array(), array(), $requestBody);

        $this->response = $this->client->getResponse()->getContent();
        $this->debugSection('Response', $this->response);

    }
    /**
     * Checks weather last response was valid JSON.
     * This is done with json_last_error function.
     *
     */
    public function seeResponseIsJson()
    {
        json_decode($this->response);
        \PHPUnit_Framework_Assert::assertEquals(
            0,
            $num = json_last_error(),
            "json decoding error #$num, see http://php.net/manual/en/function.json-last-error.php"
        );
    }
    /**
     * Checks weather the last response contains text.
     *
     * @param $text
     */
    public function seeResponseContains($text)
    {
        \PHPUnit_Framework_Assert::assertContains($text, $this->response, "REST response contains");
    }

    /**
     * Checks weather last response do not contain text.
     *
     * @param $text
     */
    public function dontSeeResponseContains($text)
    {
        \PHPUnit_Framework_Assert::assertNotContains($text, $this->response, "REST response contains");
    }

    /**
     * Checks weather the last JSON response contains provided array.
     * The response is converted to array with json_decode($response, true)
     * Thus, JSON is represented by associative array.
     * This method matches that response array contains provided array.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // response: {name: john, email: john@gmail.com}
     * $I->seeResponseContainsJson(array('name' => 'john'));
     *
     * // response {user: john, profile: { email: john@gmail.com }}
     * $I->seeResponseContainsJson(array('email' => 'john@gmail.com'));
     *
     * ?>
     * ```
     *
     * This method recursively checks if one array can be found inside of another.
     *
     * @param array $json
     */
    public function seeResponseContainsJson($json = array())
    {
        $resp_json = json_decode($this->response, true);
        \PHPUnit_Framework_Assert::assertTrue(
            $this->arrayHasArray($json, $resp_json),
            "response JSON matches provided"
        );
    }

    /**
     * Returns current response so that it can be used in next scenario steps.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $user_id = $I->grabResponse();
     * $I->sendPUT('/user', array('id' => $user_id, 'name' => 'davert'));
     * ?>
     * ```
     *
     * @version 1.1
     * @return string
     */
    public function grabResponse()
    {
        return $this->response;
    }

    /**
     * Returns data from the current JSON response using specified path
     * so that it can be used in next scenario steps
     *
     * Example:
     *
     * ``` php
     * <?php
     * $user_id = $I->grabDataFromJsonResponse('user.user_id');
     * $I->sendPUT('/user', array('id' => $user_id, 'name' => 'davert'));
     * ?>
     * ```
     *
     * @param string $path
     *
     * @since 1.1.2
     * @return string
     *
     * @author tiger.seo@gmail.com
     */
    public function grabDataFromJsonResponse($path)
    {
        $data = $response = json_decode($this->response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Response is not of JSON format or is malformed');
            $this->debugSection('Response', $this->response);
        }

        if ($path === '') {
            return $data;
        }

        foreach (explode('.', $path) as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                $this->fail('Response does not have required data');
                $this->debugSection('Response', $response);
            }

            $data = $data[$key];
        }

        return $data;
    }

    /**
     * @author nleippe@integr8ted.com
     * @author tiger.seo@gmail.com
     * @link http://www.php.net/manual/en/function.array-intersect-assoc.php#39822
     *
     * @param mixed $arr1
     * @param mixed $arr2
     *
     * @return array|bool
     */
    private function arrayIntersectAssocRecursive($arr1, $arr2)
    {
        if (!is_array($arr1) || !is_array($arr2)) {
            return $arr1 == $arr2 ? $arr1 : null;
        }
        $commonkeys = array_intersect(array_keys($arr1), array_keys($arr2));
        $ret = array();
        foreach ($commonkeys as $key) {
            $_return = $this->arrayIntersectAssocRecursive($arr1[$key], $arr2[$key]);
            if ($_return !== null) {
                $ret[$key] = $_return;
            }
        }
        return $ret;
    }

    protected function arrayHasArray(array $needle, array $haystack)
    {
        return $needle == $this->arrayIntersectAssocRecursive($needle, $haystack);
    }

    /**
     * Opposite to seeResponseContainsJson
     *
     * @param array $json
     */
    public function dontSeeResponseContainsJson($json = array())
    {
        $resp_json = json_decode($this->response, true);
        \PHPUnit_Framework_Assert::assertFalse(
            $this->arrayHasArray($json, $resp_json),
            "response JSON matches provided"
        );
    }
}
