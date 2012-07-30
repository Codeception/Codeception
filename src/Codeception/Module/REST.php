<?php
namespace Codeception\Module;

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

    protected $config = array('url' => "");
    /**
     * @var \Symfony\Component\BrowserKit\Client
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
                if (!$this->hasModule('PhpBrowser'))
                    throw new \Codeception\Exception\ModuleConfig(__CLASS__, "For REST testing via HTTP please enable PhpBrowser module");
                $this->client = $this->getModule('PhpBrowser')->session->getDriver()->getClient();
            }
            if (!$this->client) throw new \Codeception\Exception\ModuleConfig(__CLASS__, "Client for REST requests not initialized.\nProvide either PhpBrowser module, or a framework module which shares FrameworkInterface");
        }

        $this->headers = array();
        $this->params = array();
        $this->response = "";

    }

    /**
     * Sets HTTP header
     *
     * @param $name
     * @param $value
     */
    public function haveHttpHeader($name, $value) {
        $this->headers[$name] = $value;
    }


    /**
     * Adds HTTP authentication via username/password.
     *
     * @param $username
     * @param $password
     */
    public function amHttpAuthenticated($username, $password) {
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
    public function sendPOST($url, $params = array(), $files = array()) {
        $this->execute('POST', $url, $params, $files);
    }

    /**
     * Sends a GET request to given uri.
     *
     * @param $url
     * @param array $params
     */
    public function sendGET($url, $params = array()) {
        $this->execute('GET', $url, $params);
    }

    /**
     * Sends PUT request to given uri.
     *
     * @param $url
     * @param array $params
     * @param array $files
     */
    public function sendPUT($url, $params = array(), $files = array()) {
        $this->execute('PUT', $url, $params, $files);
    }

    /**
     * Sends DELETE request to given uri.
     *
     * @param $url
     * @param array $params
     * @param array $files
     */
    public function sendDELETE($url, $params = array(), $files = array()) {
        $this->execute('DELETE', $url, $params, $files);
    }

    protected function execute($method='GET', $url, $parameters=array(), $files = array())
    {
        foreach ($this->headers as $header => $val) {
            $this->client->setServerParameter("HTTP_$header", $val);
        }
        $url = $this->config['url'].$url;
        $this->debugSection("Request","$method $url?".http_build_query($parameters));
        $this->client->request($method, $url, $parameters, $files);
        $this->response = $this->client->getResponse()->getContent();
        $this->debugSection("Response", $this->response);
    }

    /**
     * Checks weather last response was valid JSON.
     * This is done with json_last_error function.
     *
     */
    public function seeResponseIsJson() {
        json_decode($this->response);
        \PHPUnit_Framework_Assert::assertEquals(0, $num = json_last_error(),"json decoding error #$num, see http://php.net/manual/en/function.json-last-error.php");
    }

    /**
     * Checks weather the last response contains text.
     *
     * @param $text
     */
    public function seeResponseContains($text) {
        \PHPUnit_Framework_Assert::assertContains($text, $this->response, "REST response contains");
    }

    /**
     * Checks weather last response do not contain text.
     *
     * @param $text
     */
    public function dontSeeResponseContains($text) {
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
    public function seeResponseContainsJson($json = array()) {
        $resp_json = json_decode($this->response,true);
        \PHPUnit_Framework_Assert::assertTrue($this->arrayHasArray($json, $resp_json), "response JSON matches provided");
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
	private function arrayIntersectAssocRecursive($arr1, $arr2) {
		if (! is_array($arr1) || ! is_array($arr2)) {
			return $arr1 == $arr2 ? $arr1 : null;
		}
		$commonkeys = array_intersect(array_keys($arr1), array_keys($arr2));
		$ret        = array();
		foreach ($commonkeys as $key) {
			$_return = $this->arrayIntersectAssocRecursive($arr1[$key], $arr2[$key]);
			if ($_return !== null) {
				$ret[$key] = $_return;
			}
		}
		return $ret;
	}

	protected function arrayHasArray(array $needle, array $haystack) {
        return $needle == $this->arrayIntersectAssocRecursive($needle, $haystack);
    }

    /**
     * Opposite to seeResponseContainsJson
     *
     * @param array $json
     */
    public function dontSeeResponseContainsJson($json = array()) {
        $resp_json = json_decode($this->response,true);
        \PHPUnit_Framework_Assert::assertFalse($this->arrayHasArray($json, $resp_json), "response JSON matches provided");
    }

    /**
     * Checks if response is exectly the same as provided.
     *
     * @param $response
     */
    public function seeResponseEquals($response) {
        \PHPUnit_Framework_Assert::assertEquals($response, $this->$response);
    }

    /**
     * Checks response code.
     *
     * @param $num
     */
    public function seeResponseCodeIs($num) {
        \PHPUnit_Framework_Assert::assertEquals($num, $this->client->getResponse()->getStatus());
    }

}
