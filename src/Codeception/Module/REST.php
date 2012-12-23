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
 *
 */
class REST extends \Codeception\Module
{
    protected $config = array(
        'url' => '',
        'xdebug_remote' => false,
        'xdebug_codecoverage' => false,
    );

    /**
     * @var \Symfony\Component\BrowserKit\Client|\Behat\Mink\Driver\Goutte\Client
     */
    public $client = null;
    public $is_functional = false;

    public $headers = array();
    public $params = array();
    public $response = "";

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
                    throw new ModuleConfigException(__CLASS__, "For REST testing via HTTP please enable PhpBrowser module");
                }
                $this->client = $this->getModule('PhpBrowser')->session->getDriver()->getClient();
            }
            if (!$this->client) {
                throw new ModuleConfigException(__CLASS__, "Client for REST requests not initialized.\nProvide either PhpBrowser module, or a framework module which shares FrameworkInterface");
            }
        }

        $this->headers = array();
        $this->params = array();
        $this->response = "";

        $this->client->setServerParameters(array());

    }

    public function _afterSuite($suite)
    {
        if ($this->config['xdebug_codecoverage']) {
            // Create a stream
            $options = array(
                'http' => array('header' => "X-Codeception-CodeCoverage: let me in\r\n")
            );
            $context = stream_context_create($options);
            $url = $this->config['url'] . '/c3/report/html';

            $tempFile = str_replace('.', '', tempnam(sys_get_temp_dir(), 'C3')) . '.tar';
            file_put_contents($tempFile, file_get_contents($url, null, $context));

            $destDir = \Codeception\Configuration::logDir() . 'codecoverage';

            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            } else {
                \Codeception\Util\FileSystem::doEmptyDir($destDir);
            }

            $phar = new \PharData($tempFile);
            $phar->extractTo($destDir);

            unlink($tempFile);
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
     * Adds HTTP authentication via username/password.
     *
     * @param $username
     * @param $password
     */
    public function amHttpAuthenticated($username, $password)
    {
        if ($this->is_functional) {
            $this->client->setServerParameter('PHP_AUTH_USER', $username);
            $this->client->setServerParameter('PHP_AUTH_PW', $password);
        } else {
            $this->client->setAuth($username, $password);
        }
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

    protected function execute($method = 'GET', $url, $parameters = array(), $files = array())
    {
        foreach ($this->headers as $header => $val) {
            $this->client->setServerParameter("HTTP_$header", $val);
        }

        // allow full url to be requested
        $url = (strpos($url, '://') === false ? $this->config['url'] : '') . $url;

        if (is_array($parameters) || $parameters instanceof \ArrayAccess) {
            $parameters = $this->scalarizeArray($parameters);
            if (array_key_exists('Content-Type', $this->headers)
                && $this->headers['Content-Type'] === 'application/json'
                && $method != 'GET'
            ) {
                $parameters = json_encode($parameters);
            }
        }

        if (is_array($parameters) || $method == 'GET') {
            if ($method == 'GET' && !empty($parameters)) {
                $url .= '?' . http_build_query($parameters);
                $this->debugSection("Request", "$method $url");
            } else {
                $this->debugSection("Request", "$method $url?" . http_build_query($parameters));
            }

            $this->client->request($method, $url, $parameters, $files);
        } else {
            $this->debugSection("Request", "$method $url " . $parameters);
            $this->client->request($method, $url, array(), $files, array(), $parameters);
        }

        $this->response = $this->client->getResponse()->getContent();
        $this->debugSection("Response", $this->response);
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
            $this->fail('Respose is not of JSON format or is malformed');
            $this->debugSection('Response', $this->response);
        }

        foreach (explode('.', $path) as $key) {
            if (!array_key_exists($key, $data)) {
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

    /**
     * Checks if response is exactly the same as provided.
     *
     * @param $response
     */
    public function seeResponseEquals($response)
    {
        \PHPUnit_Framework_Assert::assertEquals($response, $this->$response);
    }

    /**
     * Checks response code.
     *
     * @param $num
     */
    public function seeResponseCodeIs($num)
    {
        \PHPUnit_Framework_Assert::assertEquals($num, $this->client->getResponse()->getStatus());
    }
}
