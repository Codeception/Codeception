<?php

namespace Codeception\Module;

use Symfony\Component\BrowserKit\Cookie;
use Codeception\Exception\ModuleConfig as ModuleConfigException;

/**
 * Module for testing REST WebService.
 *
 * This module can be used either with frameworks or PHPBrowser.
 * It tries to guess the framework is is attached to.
 *
 * Whether framework is used it operates via standard framework modules.
 * Otherwise sends raw HTTP requests to url via PHPBrowser.
 *
 * ## Status
 *
 * * Maintainer: **tiger-seo**, **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 * * Contact: tiger.seo@gmail.com
 *
 * ## Configuration
 *
 * * url *optional* - the url of api
 *
 * This module requires PHPBrowser or any of Framework modules enabled.
 *
 * ### Example
 *
 *     modules:
 *        enabled: [PhpBrowser, REST]
 *        config:
 *           PHPBrowser:
                url: http://serviceapp/
 *           REST:
 *              url: 'http://serviceapp/api/v1/'
 *
 * ## Public Properties
 *
 * * headers - array of headers going to be sent.
 * * params - array of sent data
 * * response - last response (string)
 *
 *
 */
class REST extends \Codeception\Module
{
    protected $config = array(
        'url'                 => '',
        'xdebug_remote'       => false
    );

    /**
     * @var \Symfony\Component\HttpKernel\Client|\Symfony\Component\BrowserKit\Client
     */
    public $client = null;
    public $isFunctional = false;

    public $headers = array();
    public $params = array();
    public $response = "";

    public function _before(\Codeception\TestCase $test)
    {
        if (!$this->client) {
            if (!strpos($this->config['url'], '://')) {
                // not valid url
                foreach ($this->getModules() as $module) {
                    if ($module instanceof \Codeception\Lib\Framework) {
                        $this->client = $module->client;
                        $this->isFunctional = true;
                        $this->client->setServerParameters(array());
                        break;
                    }
                }
            } else {
                if (!$this->hasModule('PhpBrowser')) {
                    throw new ModuleConfigException(__CLASS__, "For REST testing via HTTP please enable PhpBrowser module");
                }
                $this->client = &$this->getModule('PhpBrowser')->client;
            }
        }

        $this->headers = array();
        $this->params = array();
        $this->response = "";


        if ($this->config['xdebug_remote']
            && function_exists('xdebug_is_enabled')
            && ini_get('xdebug.remote_enable')
            && !$this->isFunctional
        ) {
            $cookie = new Cookie('XDEBUG_SESSION', $this->config['xdebug_remote'], null, '/');
            $this->client->getCookieJar()->set($cookie);
        }
    }

    /**
     * Sets HTTP header
     *
     * @param $name
     * @param $value
     */
    public function haveHttpHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Checks over the given HTTP header and (optionally)
     * its value, asserting that are there
     *
     * @param $name
     * @param $value
     */
    public function seeHttpHeader($name, $value = null)
    {
        if ($value) {
            \PHPUnit_Framework_Assert::assertEquals(
                $this->client->getInternalResponse()->getHeader($name),
                $value
            );
        }
        else {
            \PHPUnit_Framework_Assert::assertNotNull($this->client->getInternalResponse()->getHeader($name));
        }
    }

    /**
     * Checks over the given HTTP header and (optionally)
     * its value, asserting that are not there
     *
     * @param $name
     * @param $value
     */
    public function dontSeeHttpHeader($name, $value = null) {
        if ($value) {
            \PHPUnit_Framework_Assert::assertNotEquals(
                $this->client->getInternalResponse()->getHeader($name),
                $value
            );
        }
        else {
            \PHPUnit_Framework_Assert::assertNull($this->client->getInternalResponse()->getHeader($name));
        }
    }

    /**
     * Checks that http response header is received only once.
     * HTTP RFC2616 allows multiple response headers with the same name.
     * You can check that you didn't accidentally sent the same header twice.
     *
     * ``` php
     * <?php
     * $I->seeHttpHeaderOnce('Cache-Control');
     * ?>>
     * ```
     *
     * @param $name
     */
    public function seeHttpHeaderOnce($name)
    {
        $headers = $this->client->getInternalResponse()->getHeader($name, false);
        $this->assertEquals(1, count($headers));
    }

    /**
     * Returns the value of the specified header name
     *
     * @param $name
     * @param Boolean $first  Whether to return the first value or all header values
     *
     * @return string|array The first header value if $first is true, an array of values otherwise
     */
    public function grabHttpHeader($name, $first = true) {
        return $this->client->getInternalResponse()->getHeader($name, $first);
    }

    /**
     * Adds HTTP authentication via username/password.
     *
     * @param $username
     * @param $password
     */
    public function amHttpAuthenticated($username, $password)
    {
        if ($this->isFunctional) {
            $this->client->setServerParameter('PHP_AUTH_USER', $username);
            $this->client->setServerParameter('PHP_AUTH_PW', $password);
        } else {
            $this->client->setAuth($username, $password);
        }
    }

	/**
	 * Adds Digest authentication via username/password.
	 *
	 * @param $username
	 * @param $password
	 */
	public function amDigestAuthenticated($username, $password)
	{
		$this->client->setAuth($username, $password, CURLAUTH_DIGEST);
	}

    /**
     * Sends a POST request to given uri.
     *
     * Parameters and files (as array of filenames) can be provided.
     *
     * @param $url
     * @param array $params
     * @param array $files
     */
    public function sendPOST($url, $params = array(), $files = array())
    {
        $this->execute('POST', $url, $params, $files);
    }

    /**
     * Sends a HEAD request to given uri.
     *
     * @param $url
     * @param array $params
     */
    public function sendHEAD($url, $params = array())
    {
        $this->execute('HEAD', $url, $params);
    }

    /**
     * Sends an OPTIONS request to given uri.
     *
     * @param $url
     * @param array $params
     */
    public function sendOPTIONS($url, $params = array())
    {
        $this->execute('OPTIONS', $url, $params);
    }

    /**
     * Sends a GET request to given uri.
     *
     * @param $url
     * @param array $params
     */
    public function sendGET($url, $params = array())
    {
        $this->execute('GET', $url, $params);
    }

    /**
     * Sends PUT request to given uri.
     *
     * @param $url
     * @param array $params
     * @param array $files
     */
    public function sendPUT($url, $params = array(), $files = array())
    {
        $this->execute('PUT', $url, $params, $files);
    }

    /**
     * Sends PATCH request to given uri.
     *
     * @param       $url
     * @param array $params
     * @param array $files
     */
    public function sendPATCH($url, $params = array(), $files = array())
    {
        $this->execute('PATCH', $url, $params, $files);
    }

    /**
     * Sends DELETE request to given uri.
     *
     * @param $url
     * @param array $params
     * @param array $files
     */
    public function sendDELETE($url, $params = array(), $files = array())
    {
        $this->execute('DELETE', $url, $params, $files);
    }

    /**
     * Sets Headers "Link" as one header "Link" based on linkEntries
     *
     * @param array $linkEntries (entry is array with keys "uri" and "link-param")
     *
     * @link http://tools.ietf.org/html/rfc2068#section-19.6.2.4
     *
     * @author samva.ua@gmail.com
     */
    private function setHeaderLink(array $linkEntries)
    {
        $values = array();
        foreach ($linkEntries as $linkEntry) {
            \PHPUnit_Framework_Assert::assertArrayHasKey(
                'uri',
                $linkEntry,
                'linkEntry should contain property "uri"'
            );
            \PHPUnit_Framework_Assert::assertArrayHasKey(
                'link-param',
                $linkEntry,
                'linkEntry should contain property "link-param"'
            );
            $values[] = $linkEntry['uri'] . '; ' . $linkEntry['link-param'];
        }

        $this->headers['Link'] = join(', ', $values);
    }

    /**
     * Sends LINK request to given uri.
     *
     * @param       $url
     * @param array $linkEntries (entry is array with keys "uri" and "link-param")
     *
     * @link http://tools.ietf.org/html/rfc2068#section-19.6.2.4
     *
     * @author samva.ua@gmail.com
     */
    public function sendLINK($url, array $linkEntries)
    {
        $this->setHeaderLink($linkEntries);
        $this->execute('LINK', $url);
    }

    /**
     * Sends UNLINK request to given uri.
     *
     * @param       $url
     * @param array $linkEntries (entry is array with keys "uri" and "link-param")
     * @link http://tools.ietf.org/html/rfc2068#section-19.6.2.4
     * @author samva.ua@gmail.com
     */
    public function sendUNLINK($url, array $linkEntries)
    {
        $this->setHeaderLink($linkEntries);
        $this->execute('UNLINK', $url);
    }

    protected function execute($method = 'GET', $url, $parameters = array(), $files = array())
    {
        foreach ($this->headers as $header => $val) {
            $header = str_replace('-','_',strtoupper($header));
            $this->client->setServerParameter("HTTP_$header", $val);

            # Issue #827 - symfony foundation requires 'CONTENT_TYPE' without HTTP_
            if ($this->isFunctional and $header == 'CONTENT_TYPE') {
                $this->client->setServerParameter($header, $val);
            }
        }

        // allow full url to be requested
        $url = (strpos($url, '://') === false ? $this->config['url'] : '') . $url;

        $parameters = $this->encodeApplicationJson($method, $parameters);

        if (is_array($parameters) || $method == 'GET') {
            if (!empty($parameters) && $method == 'GET') {
                $url .= '?' . http_build_query($parameters);
            }
            if($method == 'GET') {
                $this->debugSection("Request", "$method $url");
            } else {
                $this->debugSection("Request", "$method $url ".json_encode($parameters));
            }
            $this->client->request($method, $url, $parameters, $files);

        } else {
            $this->debugSection("Request", "$method $url " . $parameters);
            $this->client->request($method, $url, array(), $files, array(), $parameters);
        }
        $this->response = (string)$this->client->getInternalResponse()->getContent();
        $this->debugSection("Response", $this->response);

        if (count($this->client->getInternalRequest()->getCookies())) {
            $this->debugSection('Cookies', $this->client->getInternalRequest()->getCookies());
        }
        $this->debugSection("Headers", $this->client->getInternalResponse()->getHeaders());
        $this->debugSection("Status", $this->client->getInternalResponse()->getStatus());
    }

    protected function encodeApplicationJson($method, $parameters)
    {
        if (is_array($parameters) || $parameters instanceof \ArrayAccess) {
            $parameters = $this->scalarizeArray($parameters);
            if (array_key_exists('Content-Type', $this->headers)
                && $this->headers['Content-Type'] === 'application/json'
                && $method != 'GET'
            ) {
                return json_encode($parameters);
            }
        }
        return $parameters;
    }

    /**
     * Checks whether last response was valid JSON.
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
     * Checks whether last response was valid XML.
     * This is done with libxml_get_last_error function.
     *
     */
    public function seeResponseIsXml()
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($this->response);
        $num="";
        $title="";
        if ($doc===false) {
            $error = libxml_get_last_error();
            $num=$error->code;
            $title=trim($error->message);
            libxml_clear_errors();
        }
        libxml_use_internal_errors(false);
        \PHPUnit_Framework_Assert::assertNotSame(false,
            $doc ,
            "xml decoding error #$num with message \"$title\", see http://www.xmlsoft.org/html/libxml-xmlerror.html"
        );
    }

    /**
     * Checks whether the last response contains text.
     *
     * @param $text
     */
    public function seeResponseContains($text)
    {
        \PHPUnit_Framework_Assert::assertContains($text, $this->response, "REST response contains");
    }

    /**
     * Checks whether last response do not contain text.
     *
     * @param $text
     */
    public function dontSeeResponseContains($text)
    {
        \PHPUnit_Framework_Assert::assertNotContains($text, $this->response, "REST response contains");
    }

    /**
     * Checks whether the last JSON response contains provided array.
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
            "Response JSON contains provided\n"
            ."- ".print_r($json, true)
            ."+ ".print_r($resp_json, true)
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
            return null;
        }

        $commonkeys = array_intersect(array_keys($arr1), array_keys($arr2));
        $ret = array();
        foreach ($commonkeys as $key) {
            $_return = $this->arrayIntersectAssocRecursive($arr1[$key], $arr2[$key]);
            if ($_return) {
                $ret[$key] = $_return;
                continue;
            }
            if ($arr1[$key] === $arr2[$key]) {
                $ret[$key] = $arr1[$key];
            }
        }
        if (empty($commonkeys)) {
            foreach ($arr2 as $arr) {
                $_return = $this->arrayIntersectAssocRecursive($arr1, $arr);
                if ($_return && $_return == $arr1) return $_return;
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
            "Response JSON does not contain JSON provided\n"
            ."- ".print_r($json, true)
            ."+ ".print_r($resp_json, true)
        );
    }

    /**
     * Checks if response is exactly the same as provided.
     *
     * @param $response
     */
    public function seeResponseEquals($response)
    {
        \PHPUnit_Framework_Assert::assertEquals($response, $this->response);
    }

    /**
     * Checks response code equals to provided value.
     *
     * @param $code
     */
    public function seeResponseCodeIs($code)
    {
        $this->assertEquals($code, $this->client->getInternalResponse()->getStatus());
    }

    /**
     * Checks that response code is not equal to provided value.
     *
     * @param $code
     */
    public function dontSeeResponseCodeIs($code)
    {
        $this->assertNotEquals($code, $this->client->getInternalResponse()->getStatus());
    }

}
