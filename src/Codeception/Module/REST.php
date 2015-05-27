<?php

namespace Codeception\Module;

use Codeception\Exception\Module;
use Codeception\Lib\Framework;
use Codeception\Lib\InnerBrowser;
use Codeception\Util\JsonArray;
use Symfony\Component\BrowserKit\Cookie;

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
 *           PhpBrowser:
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
     * @var \Symfony\Component\BrowserKit\Client
     */
    public $client = null;
    public $isFunctional = false;
    protected $connectionModule;

    public $headers = array();
    public $params = array();
    public $response = "";


    public function _before(\Codeception\TestCase $test)
    {
        $this->prepareConnection();
        $this->client = &$this->connectionModule->client;
        $this->resetVariables();

        if ($this->config['xdebug_remote']
            && function_exists('xdebug_is_enabled')
            && ini_get('xdebug.remote_enable')
            && !$this->isFunctional
        ) {
            $cookie = new Cookie('XDEBUG_SESSION', $this->config['xdebug_remote'], null, '/');
            $this->client->getCookieJar()->set($cookie);
        }
    }

    protected function resetVariables()
    {
        $this->headers = array();
        $this->params = array();
        $this->response = "";
        if ($this->client) {
            $this->client->setServerParameters(array());
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
        if ($value !== null) {
            $this->assertEquals(
                $this->client->getInternalResponse()->getHeader($name),
                $value
            );
            return;
        }
        $this->assertNotNull($this->client->getInternalResponse()->getHeader($name));
    }

    /**
     * Checks over the given HTTP header and (optionally)
     * its value, asserting that are not there
     *
     * @param $name
     * @param $value
     */
    public function dontSeeHttpHeader($name, $value = null) {
        if ($value !== null) {
            $this->assertNotEquals(
                $this->client->getInternalResponse()->getHeader($name),
                $value
            );
            return;
        }
        $this->assertNull($this->client->getInternalResponse()->getHeader($name));
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
     * Adds Bearer authentication via access token.
     *
     * @param $accessToken
     */
    public function amBearerAuthenticated($accessToken)
    {
        $this->haveHttpHeader('Authorization', 'Bearer '.$accessToken);
    }

    /**
     * Sends a POST request to given uri.
     *
     * Parameters and files (as array of filenames) can be provided.
     *
     * @param $url
     * @param array|\JsonSerializable $params
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
        $this->debugSection("Request headers", $this->headers);

        if ($parameters instanceof \JsonSerializable) {
            $parameters = $parameters->jsonSerialize();
        }

        foreach ($this->headers as $header => $val) {
            $header = str_replace('-','_',strtoupper($header));
            $this->client->setServerParameter("HTTP_$header", $val);

            // Issue #1650 - Symfony BrowserKit changes HOST header to request URL
            if (strtolower($header) == 'host') {
                $this->client->setServerParameter("HTTP_ HOST", $val);
            }

            // Issue #827 - symfony foundation requires 'CONTENT_TYPE' without HTTP_
            if ($this->isFunctional and $header == 'CONTENT_TYPE') {
                $this->client->setServerParameter($header, $val);
            }
        }

        // allow full url to be requested
        $url = (strpos($url, '://') === false ? $this->config['url'] : '') . $url;

        $this->params = $parameters;
        
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
        if (array_key_exists('Content-Type', $this->headers)
            && $this->headers['Content-Type'] === 'application/json'
            && $method != 'GET'
        ) {
            if ($parameters instanceof \JsonSerializable) {
                return json_encode($parameters);
            }
            if (is_array($parameters) || $parameters instanceof \ArrayAccess) {
                $parameters = $this->scalarizeArray($parameters);
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
        $this->assertContains($text, $this->response, "REST response contains");
    }

    /**
     * Checks whether last response do not contain text.
     *
     * @param $text
     */
    public function dontSeeResponseContains($text)
    {
        $this->assertNotContains($text, $this->response, "REST response contains");
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
        $jsonResponseArray = new JsonArray($this->response);
        \PHPUnit_Framework_Assert::assertTrue(
            $jsonResponseArray->containsArray($json),
            "Response JSON contains provided\n"
            ."- <info>".var_export($json, true)."</info>\n"
            ."+ ".var_export($jsonResponseArray->toArray(), true)
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
     * so that it can be used in next scenario steps.
     *
     * **this method is deprecated in favor of `grabDataFromResponseByJsonPath`**
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
     * @deprecated please use `grabDataFromResponseByJsonPath`
     * @param string $path
     * @return string
     */
    public function grabDataFromJsonResponse($path = '')
    {
        $data = $response = json_decode($this->response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->debugSection('Response', $this->response);
            $this->fail('Response is not of JSON format or is malformed');
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
     * Returns data from the current JSON response using [JSONPath](http://goessner.net/articles/JsonPath/) as selector.
     * JsonPath is XPath equivalent for querying Json structures. Try your JsonPath expressions [online](http://jsonpath.curiousconcept.com/).
     * Even for a single value an array is returned.
     *
     * This method **require [`flow/jsonpath` > 0.2](https://github.com/FlowCommunications/JSONPath/) library to be installed**.
     *
     * Example:
     *
     * ``` php
     * <?php
     * // match the first `user.id` in json
     * $firstUser = $I->grabDataFromJsonResponse('$..users[0].id');
     * $I->sendPUT('/user', array('id' => $firstUser[0], 'name' => 'davert'));
     * ?>
     * ```
     *
     * @param $jsonPath
     * @return array
     * @version 2.0.9
     * @throws \Exception
     */
    public function grabDataFromResponseByJsonPath($jsonPath)
    {
        return (new JsonArray($this->response))->filterByJsonPath($jsonPath);
    }

    /**
     * Checks if json structure in response matches the xpath provided.
     * JSON is not supposed to be checked against XPath, yet it can be converted to xml and used with XPath.
     * This assertion allows you to check the structure of response json.
     *     *
     * ```json
     *   { "store": {
     *       "book": [
     *         { "category": "reference",
     *           "author": "Nigel Rees",
     *           "title": "Sayings of the Century",
     *           "price": 8.95
     *         },
     *         { "category": "fiction",
     *           "author": "Evelyn Waugh",
     *           "title": "Sword of Honour",
     *           "price": 12.99
     *         }
     *    ],
     *       "bicycle": {
     *         "color": "red",
     *         "price": 19.95
     *       }
     *     }
     *   }
     * ```
     *
     * ```php
     * <?php
     * // at least one book in store has author
     * $I->seeResponseJsonMatchesXpath('//store/book/author');
     * // first book in store has author
     * $I->seeResponseJsonMatchesXpath('//store/book[1]/author');
     * // at least one item in store has price
     * $I->seeResponseJsonMatchesXpath('/store//price');
     * ?>
     * ```
     *
     * @version 2.0.9
     */
    public function seeResponseJsonMatchesXpath($xpath)
    {
        $this->assertGreaterThan(0, (new JsonArray($this->response))->filterByXPath($xpath)->length,
            "Received JSON did not match the XPath `$xpath`.\nJson Response: \n".$this->response);
    }

    /**
     * Checks if json structure in response matches [JsonPath](http://goessner.net/articles/JsonPath/).
     * JsonPath is XPath equivalent for querying Json structures. Try your JsonPath expressions [online](http://jsonpath.curiousconcept.com/).
     * This assertion allows you to check the structure of response json.
     *
     * This method **require [`flow/jsonpath` > 0.2](https://github.com/FlowCommunications/JSONPath/) library to be installed**.
     *
     * ```json
     *   { "store": {
     *       "book": [
     *         { "category": "reference",
     *           "author": "Nigel Rees",
     *           "title": "Sayings of the Century",
     *           "price": 8.95
     *         },
     *         { "category": "fiction",
     *           "author": "Evelyn Waugh",
     *           "title": "Sword of Honour",
     *           "price": 12.99
     *         }
     *    ],
     *       "bicycle": {
     *         "color": "red",
     *         "price": 19.95
     *       }
     *     }
     *   }
     * ```
     *
     * ```php
     * <?php
     * // at least one book in store has author
     * $I->seeResponseJsonMatchesJsonPath('$.store.book[*].author');
     * // first book in store has author
     * $I->seeResponseJsonMatchesJsonPath('$.store.book[0].author');
     * // at least one item in store has price
     * $I->seeResponseJsonMatchesJsonPath('$.store..price');
     * ?>
     * ```
     *
     * @version 2.0.9
     */
    public function seeResponseJsonMatchesJsonPath($jsonPath)
    {
        $this->assertNotEmpty((new JsonArray($this->response))->filterByJsonPath($jsonPath),
            "Received JSON did not match the JsonPath provided\n".$this->response);
    }

    /**
     * Opposite to seeResponseJsonMatchesJsonPath
     *
     * @param array $jsonPath
     */
    public function dontSeeResponseJsonMatchesJsonPath($jsonPath)
    {
        $this->assertEmpty((new JsonArray($this->response))->filterByJsonPath($jsonPath),
            "Received JSON did (but should not) match the JsonPath provided\n".$this->response);
    }

    /**
     * Opposite to seeResponseContainsJson
     *
     * @param array $json
     */
    public function dontSeeResponseContainsJson($json = array())
    {
        $jsonResponseArray = new JsonArray($this->response);
        \PHPUnit_Framework_Assert::assertFalse(
            $jsonResponseArray->containsArray($json),
            "Response JSON does not contain JSON provided\n"
            ."- <info>".var_export($json, true)."</info>\n"
            ."+ ".var_export($jsonResponseArray->toArray(), true)
        );
    }

    /**
     * Checks if response is exactly the same as provided.
     *
     * @param $response
     */
    public function seeResponseEquals($response)
    {
        $this->assertEquals($response, $this->response);
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

    protected function prepareConnection()
    {
        if ($this->connectionModule) {
            return;
        }
        foreach ($this->getModules() as $module) {
            if ($module instanceof InnerBrowser) {
                $this->connectionModule = $module;
                break;
            }
        }

        if (!$this->connectionModule) {
            throw new Module(__CLASS__, "Provide either PHPBrowser or one of Framework modules to be able to send REST requests");
        }

        if ($this->connectionModule instanceof Framework) {
            $this->isFunctional = true;
        }
    }

}
