<?php

namespace Codeception\Module;

use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Module as CodeceptionModule;
use Codeception\PHPUnit\Constraint\JsonContains;
use Codeception\PHPUnit\Constraint\JsonType as JsonTypeConstraint;
use Codeception\TestInterface;
use Codeception\Lib\Interfaces\API;
use Codeception\Lib\Framework;
use Codeception\Lib\InnerBrowser;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Util\JsonArray;
use Codeception\Util\JsonType;
use Codeception\Util\XmlStructure;
use Codeception\Util\Soap as XmlUtils;

/**
 * Module for testing REST WebService.
 *
 * This module can be used either with frameworks or PHPBrowser.
 * If a framework module is connected, the testing will occur in the application directly.
 * Otherwise, a PHPBrowser should be specified as a dependency to send requests and receive responses from a server.
 *
 * ## Configuration
 *
 * * url *optional* - the url of api
 * * shortDebugResponse *optional* - amount of chars to limit the api response length
 *
 * This module requires PHPBrowser or any of Framework modules enabled.
 *
 * ### Example
 *
 *     modules:
 *        enabled:
 *            - REST:
 *                depends: PhpBrowser
 *                url: 'http://serviceapp/api/v1/'
 *                shortDebugResponse: 300 # only the first 300 chars of the response
 *
 * ## Public Properties
 *
 * * headers - array of headers going to be sent.
 * * params - array of sent data
 * * response - last response (string)
 *
 * ## Parts
 *
 * * Json - actions for validating Json responses (no Xml responses)
 * * Xml - actions for validating XML responses (no Json responses)
 *
 * ## Conflicts
 *
 * Conflicts with SOAP module
 *
 */
class REST extends CodeceptionModule implements DependsOnModule, PartedModule, API, ConflictsWithModule
{
    protected $config = [
        'url' => '',
        'aws' => ''
    ];

    protected $dependencyMessage = <<<EOF
Example configuring PhpBrowser as backend for REST module.
--
modules:
    enabled:
        - REST:
            depends: PhpBrowser
            url: http://localhost/api/
            shortDebugResponse: 300
--
Framework modules can be used for testing of API as well.
EOF;

    protected $DEFAULT_SHORTEN_VALUE = 150;

    /**
     * @var \Symfony\Component\HttpKernel\Client|\Symfony\Component\BrowserKit\Client
     */
    public $client = null;
    public $isFunctional = false;

    /**
     * @var InnerBrowser
     */
    protected $connectionModule;

    public $params = [];
    public $response = "";

    public function _before(TestInterface $test)
    {
        $this->client = &$this->connectionModule->client;
        $this->resetVariables();
    }

    protected function resetVariables()
    {
        $this->params = [];
        $this->response = "";
        $this->connectionModule->headers = [];
    }

    public function _conflicts()
    {
        return 'Codeception\Lib\Interfaces\API';
    }

    public function _depends()
    {
        return ['Codeception\Lib\InnerBrowser' => $this->dependencyMessage];
    }

    public function _parts()
    {
        return ['xml', 'json'];
    }

    public function _inject(InnerBrowser $connection)
    {
        $this->connectionModule = $connection;
        if ($this->connectionModule instanceof Framework) {
            $this->isFunctional = true;
        }
        if ($this->connectionModule instanceof PhpBrowser) {
            if (!$this->connectionModule->_getConfig('url')) {
                $this->connectionModule->_setConfig(['url' => $this->config['url']]);
            }
        }
    }

    public function _failed(TestInterface $test, $fail)
    {
        if (!$this->response) {
            return;
        }
        $printedResponse = $this->response;
        if ($this->isBinaryData($printedResponse)) {
            $printedResponse = $this->binaryToDebugString($printedResponse);
        }
        $test->getMetadata()->addReport('body', $printedResponse);
    }


    protected function getRunningClient()
    {
        if ($this->client->getInternalRequest() === null) {
            throw new ModuleException($this, "Response is empty. Use `\$I->sendXXX()` methods to send HTTP request");
        }
        return $this->client;
    }

    /**
     * Sets HTTP header valid for all next requests. Use `deleteHeader` to unset it
     *
     * ```php
     * <?php
     * $I->haveHttpHeader('Content-Type', 'application/json');
     * // all next requests will contain this header
     * ?>
     * ```
     *
     * @param $name
     * @param $value
     * @part json
     * @part xml
     */
    public function haveHttpHeader($name, $value)
    {
        $this->connectionModule->haveHttpHeader($name, $value);
    }

    /**
     * Deletes the header with the passed name.  Subsequent requests
     * will not have the deleted header in its request.
     *
     * Example:
     * ```php
     * <?php
     * $I->haveHttpHeader('X-Requested-With', 'Codeception');
     * $I->sendGET('test-headers.php');
     * // ...
     * $I->deleteHeader('X-Requested-With');
     * $I->sendPOST('some-other-page.php');
     * ?>
     * ```
     *
     * @param string $name the name of the header to delete.
     * @part json
     * @part xml
     */
    public function deleteHeader($name)
    {
        $this->connectionModule->deleteHeader($name);
    }

    /**
     * Checks over the given HTTP header and (optionally)
     * its value, asserting that are there
     *
     * @param $name
     * @param $value
     * @part json
     * @part xml
     */
    public function seeHttpHeader($name, $value = null)
    {
        if ($value !== null) {
            $this->assertEquals(
                $value,
                $this->getRunningClient()->getInternalResponse()->getHeader($name)
            );
            return;
        }
        $this->assertNotNull($this->getRunningClient()->getInternalResponse()->getHeader($name));
    }

    /**
     * Checks over the given HTTP header and (optionally)
     * its value, asserting that are not there
     *
     * @param $name
     * @param $value
     * @part json
     * @part xml
     */
    public function dontSeeHttpHeader($name, $value = null)
    {
        if ($value !== null) {
            $this->assertNotEquals(
                $value,
                $this->getRunningClient()->getInternalResponse()->getHeader($name)
            );
            return;
        }
        $this->assertNull($this->getRunningClient()->getInternalResponse()->getHeader($name));
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
     * @part json
     * @part xml
     */
    public function seeHttpHeaderOnce($name)
    {
        $headers = $this->getRunningClient()->getInternalResponse()->getHeader($name, false);
        $this->assertCount(1, $headers);
    }

    /**
     * Returns the value of the specified header name
     *
     * @param $name
     * @param Boolean $first Whether to return the first value or all header values
     *
     * @return string|array The first header value if $first is true, an array of values otherwise
     * @part json
     * @part xml
     */
    public function grabHttpHeader($name, $first = true)
    {
        return $this->getRunningClient()->getInternalResponse()->getHeader($name, $first);
    }

    /**
     * Adds HTTP authentication via username/password.
     *
     * @param $username
     * @param $password
     * @part json
     * @part xml
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
     * @part json
     * @part xml
     */
    public function amDigestAuthenticated($username, $password)
    {
        if ($this->isFunctional) {
            throw new ModuleException(__METHOD__, 'Not supported by functional modules');
        }
        $this->client->setAuth($username, $password, 'digest');
    }

    /**
     * Adds Bearer authentication via access token.
     *
     * @param $accessToken
     * @part json
     * @part xml
     */
    public function amBearerAuthenticated($accessToken)
    {
        $this->haveHttpHeader('Authorization', 'Bearer ' . $accessToken);
    }

    /**
     * Adds NTLM authentication via username/password.
     * Requires client to be Guzzle >=6.3.0
     * Out of scope for functional modules.
     *
     * Example:
     * ```php
     * <?php
     * $I->amNTLMAuthenticated('jon_snow', 'targaryen');
     * ?>
     * ```
     *
     * @param $username
     * @param $password
     * @throws ModuleException
     * @part json
     * @part xml
     */
    public function amNTLMAuthenticated($username, $password)
    {
        if ($this->isFunctional) {
            throw new ModuleException(__METHOD__, 'Not supported by functional modules');
        }
        if (!defined('\GuzzleHttp\Client::VERSION')) {
            throw new ModuleException(__METHOD__, 'Not supported if not using a Guzzle client');
        }
        if (version_compare(\GuzzleHttp\Client::VERSION, '6.2.1', 'lt')) {
            throw new ModuleException(__METHOD__, 'Guzzle ' . \GuzzleHttp\Client::VERSION . ' found. Requires Guzzle >=6.3.0 for NTLM auth option');
        }
        $this->client->setAuth($username, $password, 'ntlm');
    }

    /**
     * Allows to send REST request using AWS Authorization
     *
     * Only works with PhpBrowser
     * Example Config:
     * ```yml
     * modules:
     *      enabled:
     *          - REST:
     *              aws:
     *                  key: accessKey
     *                  secret: accessSecret
     *                  service: awsService
     *                  region: awsRegion
     * ```
     * Code:
     * ```php
     * <?php
     * $I->amAWSAuthenticated();
     * ?>
     * ```
     * @param array $additionalAWSConfig
     * @throws ModuleException
     */
    public function amAWSAuthenticated($additionalAWSConfig = [])
    {
        if (method_exists($this->client, 'setAwsAuth')) {
            $config = array_merge($this->config['aws'], $additionalAWSConfig);

            if (!isset($config['key'])) {
                throw new ConfigurationException('AWS Key is not set');
            }
            if (!isset($config['secret'])) {
                throw new ConfigurationException('AWS Secret is not set');
            }
            if (!isset($config['service'])) {
                throw new ConfigurationException('AWS Service is not set');
            }
            if (!isset($config['region'])) {
                throw new ConfigurationException('AWS Region is not set');
            }

            $this->client->setAwsAuth($config);
        }
    }

    /**
     * Sends a POST request to given uri. Parameters and files can be provided separately.
     *
     * Example:
     * ```php
     * <?php
     * //simple POST call
     * $I->sendPOST('/message', ['subject' => 'Read this!', 'to' => 'johndoe@example.com']);
     * //simple upload method
     * $I->sendPOST('/message/24', ['inline' => 0], ['attachmentFile' => codecept_data_dir('sample_file.pdf')]);
     * //uploading a file with a custom name and mime-type. This is also useful to simulate upload errors.
     * $I->sendPOST('/message/24', ['inline' => 0], [
     *     'attachmentFile' => [
     *          'name' => 'document.pdf',
     *          'type' => 'application/pdf',
     *          'error' => UPLOAD_ERR_OK,
     *          'size' => filesize(codecept_data_dir('sample_file.pdf')),
     *          'tmp_name' => codecept_data_dir('sample_file.pdf')
     *     ]
     * ]);
     * ```
     *
     * @param $url
     * @param array|\JsonSerializable $params
     * @param array $files A list of filenames or "mocks" of $_FILES (each entry being an array with the following
     *                     keys: name, type, error, size, tmp_name (pointing to the real file path). Each key works
     *                     as the "name" attribute of a file input field.
     *
     * @see http://php.net/manual/en/features.file-upload.post-method.php
     * @see codecept_data_dir()
     * @part json
     * @part xml
     */
    public function sendPOST($url, $params = [], $files = [])
    {
        $this->execute('POST', $url, $params, $files);
    }

    /**
     * Sends a HEAD request to given uri.
     *
     * @param $url
     * @param array $params
     * @part json
     * @part xml
     */
    public function sendHEAD($url, $params = [])
    {
        $this->execute('HEAD', $url, $params);
    }

    /**
     * Sends an OPTIONS request to given uri.
     *
     * @param $url
     * @param array $params
     * @part json
     * @part xml
     */
    public function sendOPTIONS($url, $params = [])
    {
        $this->execute('OPTIONS', $url, $params);
    }

    /**
     * Sends a GET request to given uri.
     *
     * @param $url
     * @param array $params
     * @part json
     * @part xml
     */
    public function sendGET($url, $params = [])
    {
        $this->execute('GET', $url, $params);
    }

    /**
     * Sends PUT request to given uri.
     *
     * @param $url
     * @param array $params
     * @param array $files
     * @part json
     * @part xml
     */
    public function sendPUT($url, $params = [], $files = [])
    {
        $this->execute('PUT', $url, $params, $files);
    }

    /**
     * Sends PATCH request to given uri.
     *
     * @param       $url
     * @param array $params
     * @param array $files
     * @part json
     * @part xml
     */
    public function sendPATCH($url, $params = [], $files = [])
    {
        $this->execute('PATCH', $url, $params, $files);
    }

    /**
     * Sends DELETE request to given uri.
     *
     * @param $url
     * @param array $params
     * @param array $files
     * @part json
     * @part xml
     */
    public function sendDELETE($url, $params = [], $files = [])
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
        $values = [];
        foreach ($linkEntries as $linkEntry) {
            \PHPUnit\Framework\Assert::assertArrayHasKey(
                'uri',
                $linkEntry,
                'linkEntry should contain property "uri"'
            );
            \PHPUnit\Framework\Assert::assertArrayHasKey(
                'link-param',
                $linkEntry,
                'linkEntry should contain property "link-param"'
            );
            $values[] = $linkEntry['uri'] . '; ' . $linkEntry['link-param'];
        }

        $this->haveHttpHeader('Link', implode(', ', $values));
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
     * @part json
     * @part xml
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
     * @part json
     * @part xml
     */
    public function sendUNLINK($url, array $linkEntries)
    {
        $this->setHeaderLink($linkEntries);
        $this->execute('UNLINK', $url);
    }

    protected function execute($method, $url, $parameters = [], $files = [])
    {
        // allow full url to be requested
        if (!$url) {
            $url = $this->config['url'];
        } elseif (strpos($url, '://') === false && $this->config['url']) {
            $url = rtrim($this->config['url'], '/') . '/' . ltrim($url, '/');
        }

        $this->params = $parameters;

        $parameters = $this->encodeApplicationJson($method, $parameters);

        if (is_array($parameters) || $method === 'GET') {
            if (!empty($parameters) && $method === 'GET') {
                if (strpos($url, '?') !== false) {
                    $url .= '&';
                } else {
                    $url .= '?';
                }
                $url .= http_build_query($parameters);
            }
            if ($method == 'GET') {
                $this->debugSection("Request", "$method $url");
                $files = [];
            } else {
                $this->debugSection("Request", "$method $url " . json_encode($parameters));
                $files = $this->formatFilesArray($files);
            }
            $this->response = (string)$this->connectionModule->_request($method, $url, $parameters, $files);
        } else {
            $requestData = $parameters;
            if ($this->isBinaryData($requestData)) {
                $requestData = $this->binaryToDebugString($requestData);
            }
            $this->debugSection("Request", "$method $url " . $requestData);
            $this->response = (string)$this->connectionModule->_request($method, $url, [], $files, [], $parameters);
        }
        $printedResponse = $this->response;
        if ($this->isBinaryData($printedResponse)) {
            $printedResponse = $this->binaryToDebugString($printedResponse);
        }

        $short = $this->_getConfig('shortDebugResponse');

        if (!is_null($short)) {
            $printedResponse = $this->shortenMessage($printedResponse, $short);
            $this->debugSection("Shortened Response", $printedResponse);
        } else {
            $this->debugSection("Response", $printedResponse);
        }
    }

    /**
     * Check if data has non-printable bytes and it is not a valid unicode string
     *
     * @param string $data the text or binary data string
     * @return boolean
     */
    protected function isBinaryData($data)
    {
        return !ctype_print($data) && false === mb_detect_encoding($data, mb_detect_order(), true);
    }

    /**
     * Format a binary string for debug printing
     *
     * @param string $data the binary data string
     * @return string the debug string
     */
    protected function binaryToDebugString($data)
    {
        return '[binary-data length:' . strlen($data) . ' md5:' . md5($data) . ']';
    }

    protected function encodeApplicationJson($method, $parameters)
    {
        if ($method !== 'GET' && array_key_exists('Content-Type', $this->connectionModule->headers)
            && ($this->connectionModule->headers['Content-Type'] === 'application/json'
                || preg_match('!^application/.+\+json$!', $this->connectionModule->headers['Content-Type'])
            )
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

    private function formatFilesArray(array $files)
    {
        foreach ($files as $name => $value) {
            if (is_string($value)) {
                $this->checkFileBeforeUpload($value);

                $files[$name] = [
                    'name' => basename($value),
                    'tmp_name' => $value,
                    'size' => filesize($value),
                    'type' => $this->getFileType($value),
                    'error' => 0,
                ];
                continue;
            } elseif (is_array($value)) {
                if (isset($value['tmp_name'])) {
                    $this->checkFileBeforeUpload($value['tmp_name']);
                    if (!isset($value['name'])) {
                        $value['name'] = basename($value['tmp_name']);
                    }
                    if (!isset($value['size'])) {
                        $value['size'] = filesize($value['tmp_name']);
                    }
                    if (!isset($value['type'])) {
                        $value['type'] = $this->getFileType($value['tmp_name']);
                    }
                    if (!isset($value['error'])) {
                        $value['error'] = 0;
                    }
                } else {
                    $files[$name] = $this->formatFilesArray($value);
                }
            } elseif (is_object($value)) {
                /**
                 * do nothing, probably the user knows what he is doing
                 * @issue https://github.com/Codeception/Codeception/issues/3298
                 */
            } else {
                throw new ModuleException(__CLASS__, "Invalid value of key $name in files array");
            }
        }

        return $files;
    }

    private function getFileType($file)
    {
        if (function_exists('mime_content_type') && mime_content_type($file)) {
            return mime_content_type($file);
        }
        return 'application/octet-stream';
    }

    private function checkFileBeforeUpload($file)
    {
        if (!file_exists($file)) {
            throw new ModuleException(__CLASS__, "File $file does not exist");
        }
        if (!is_readable($file)) {
            throw new ModuleException(__CLASS__, "File $file is not readable");
        }
        if (!is_file($file)) {
            throw new ModuleException(__CLASS__, "File $file is not a regular file");
        }
    }

    /**
     * Extends the function Module::validateConfig for shorten messages
     *
     */
    protected function validateConfig()
    {
        parent::validateConfig();

        $short = $this->_getConfig('shortDebugResponse');

        if (!is_null($short)) {
            if (!is_int($short) || $short < 0) {
                throw new ModuleConfigException(__CLASS__, 'The value of "shortDebugMessage" should be integer and greater or equal "0".');
            }
        }
    }

    /**
     * Checks whether last response was valid JSON.
     * This is done with json_last_error function.
     *
     * @part json
     */
    public function seeResponseIsJson()
    {
        $responseContent = $this->connectionModule->_getResponseContent();
        \PHPUnit\Framework\Assert::assertNotEquals('', $responseContent, 'response is empty');
        json_decode($responseContent);
        $errorCode = json_last_error();
        $errorMessage = json_last_error_msg();
        \PHPUnit\Framework\Assert::assertEquals(
            JSON_ERROR_NONE,
            $errorCode,
            sprintf(
                "Invalid json: %s. System message: %s.",
                $responseContent,
                $errorMessage
            )
        );
    }

    /**
     * Checks whether the last response contains text.
     *
     * @param $text
     * @part json
     * @part xml
     */
    public function seeResponseContains($text)
    {
        $this->assertStringContainsString($text, $this->connectionModule->_getResponseContent(), "REST response contains");
    }

    /**
     * Checks whether last response do not contain text.
     *
     * @param $text
     * @part json
     * @part xml
     */
    public function dontSeeResponseContains($text)
    {
        $this->assertStringNotContainsString($text, $this->connectionModule->_getResponseContent(), "REST response contains");
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
     * @part json
     */
    public function seeResponseContainsJson($json = [])
    {
        \PHPUnit\Framework\Assert::assertThat(
            $this->connectionModule->_getResponseContent(),
            new JsonContains($json)
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
     * @return string
     * @part json
     * @part xml
     * @version 1.1
     */
    public function grabResponse()
    {
        return $this->connectionModule->_getResponseContent();
    }

    /**
     * Returns data from the current JSON response using [JSONPath](http://goessner.net/articles/JsonPath/) as selector.
     * JsonPath is XPath equivalent for querying Json structures.
     * Try your JsonPath expressions [online](http://jsonpath.curiousconcept.com/).
     * Even for a single value an array is returned.
     *
     * This method **require [`flow/jsonpath` > 0.2](https://github.com/FlowCommunications/JSONPath/) library to be installed**.
     *
     * Example:
     *
     * ``` php
     * <?php
     * // match the first `user.id` in json
     * $firstUserId = $I->grabDataFromResponseByJsonPath('$..users[0].id');
     * $I->sendPUT('/user', array('id' => $firstUserId[0], 'name' => 'davert'));
     * ?>
     * ```
     *
     * @param string $jsonPath
     * @return array Array of matching items
     * @throws \Exception
     * @part json
     * @version 2.0.9
     */
    public function grabDataFromResponseByJsonPath($jsonPath)
    {
        return (new JsonArray($this->connectionModule->_getResponseContent()))->filterByJsonPath($jsonPath);
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
     * @param string $xpath
     * @part json
     * @version 2.0.9
     */
    public function seeResponseJsonMatchesXpath($xpath)
    {
        $response = $this->connectionModule->_getResponseContent();
        $this->assertGreaterThan(
            0,
            (new JsonArray($response))->filterByXPath($xpath)->length,
            "Received JSON did not match the XPath `$xpath`.\nJson Response: \n" . $response
        );
    }

    /**
     * Opposite to seeResponseJsonMatchesXpath
     *
     * @param string $xpath
     * @part json
     */
    public function dontSeeResponseJsonMatchesXpath($xpath)
    {
        $response = $this->connectionModule->_getResponseContent();
        $this->assertEquals(
            0,
            (new JsonArray($response))->filterByXPath($xpath)->length,
            "Received JSON matched the XPath `$xpath`.\nJson Response: \n" . $response
        );
    }

    /**
     * Checks if json structure in response matches [JsonPath](http://goessner.net/articles/JsonPath/).
     * JsonPath is XPath equivalent for querying Json structures.
     * Try your JsonPath expressions [online](http://jsonpath.curiousconcept.com/).
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
     * @param string $jsonPath
     * @part json
     * @version 2.0.9
     */
    public function seeResponseJsonMatchesJsonPath($jsonPath)
    {
        $response = $this->connectionModule->_getResponseContent();
        $this->assertNotEmpty(
            (new JsonArray($response))->filterByJsonPath($jsonPath),
            "Received JSON did not match the JsonPath `$jsonPath`.\nJson Response: \n" . $response
        );
    }

    /**
     * Opposite to seeResponseJsonMatchesJsonPath
     *
     * @param string $jsonPath
     * @part json
     */
    public function dontSeeResponseJsonMatchesJsonPath($jsonPath)
    {
        $response = $this->connectionModule->_getResponseContent();
        $this->assertEmpty(
            (new JsonArray($response))->filterByJsonPath($jsonPath),
            "Received JSON matched the JsonPath `$jsonPath`.\nJson Response: \n" . $response
        );
    }

    /**
     * Opposite to seeResponseContainsJson
     *
     * @part json
     * @param array $json
     */
    public function dontSeeResponseContainsJson($json = [])
    {
        $jsonResponseArray = new JsonArray($this->connectionModule->_getResponseContent());
        $this->assertFalse(
            $jsonResponseArray->containsArray($json),
            "Response JSON contains provided JSON\n"
            . "- <info>" . var_export($json, true) . "</info>\n"
            . "+ " . var_export($jsonResponseArray->toArray(), true)
        );
    }

    /**
     * Checks that Json matches provided types.
     * In case you don't know the actual values of JSON data returned you can match them by type.
     * Starts check with a root element. If JSON data is array it will check the first element of an array.
     * You can specify the path in the json which should be checked with JsonPath
     *
     * Basic example:
     *
     * ```php
     * <?php
     * // {'user_id': 1, 'name': 'davert', 'is_active': false}
     * $I->seeResponseMatchesJsonType([
     *      'user_id' => 'integer',
     *      'name' => 'string|null',
     *      'is_active' => 'boolean'
     * ]);
     *
     * // narrow down matching with JsonPath:
     * // {"users": [{ "name": "davert"}, {"id": 1}]}
     * $I->seeResponseMatchesJsonType(['name' => 'string'], '$.users[0]');
     * ?>
     * ```
     *
     * In this case you can match that record contains fields with data types you expected.
     * The list of possible data types:
     *
     * * string
     * * integer
     * * float
     * * array (json object is array as well)
     * * boolean
     *
     * You can also use nested data type structures:
     *
     * ```php
     * <?php
     * // {'user_id': 1, 'name': 'davert', 'company': {'name': 'Codegyre'}}
     * $I->seeResponseMatchesJsonType([
     *      'user_id' => 'integer|string', // multiple types
     *      'company' => ['name' => 'string']
     * ]);
     * ?>
     * ```
     *
     * You can also apply filters to check values. Filter can be applied with `:` char after the type declaration.
     *
     * Here is the list of possible filters:
     *
     * * `integer:>{val}` - checks that integer is greater than {val} (works with float and string types too).
     * * `integer:<{val}` - checks that integer is lower than {val} (works with float and string types too).
     * * `string:url` - checks that value is valid url.
     * * `string:date` - checks that value is date in JavaScript format: https://weblog.west-wind.com/posts/2014/Jan/06/JavaScript-JSON-Date-Parsing-and-real-Dates
     * * `string:email` - checks that value is a valid email according to http://emailregex.com/
     * * `string:regex({val})` - checks that string matches a regex provided with {val}
     *
     * This is how filters can be used:
     *
     * ```php
     * <?php
     * // {'user_id': 1, 'email' => 'davert@codeception.com'}
     * $I->seeResponseMatchesJsonType([
     *      'user_id' => 'string:>0:<1000', // multiple filters can be used
     *      'email' => 'string:regex(~\@~)' // we just check that @ char is included
     * ]);
     *
     * // {'user_id': '1'}
     * $I->seeResponseMatchesJsonType([
     *      'user_id' => 'string:>0', // works with strings as well
     * }
     * ?>
     * ```
     *
     * You can also add custom filters y accessing `JsonType::addCustomFilter` method.
     * See [JsonType reference](http://codeception.com/docs/reference/JsonType).
     *
     * @part json
     * @param array $jsonType
     * @param string $jsonPath
     * @version 2.1.3
     */
    public function seeResponseMatchesJsonType(array $jsonType, $jsonPath = null)
    {
        $jsonArray = new JsonArray($this->connectionModule->_getResponseContent());
        if ($jsonPath) {
            $jsonArray = $jsonArray->filterByJsonPath($jsonPath);
        }

        \PHPUnit\Framework\Assert::assertThat($jsonArray, new JsonTypeConstraint($jsonType));
    }

    /**
     * Opposite to `seeResponseMatchesJsonType`.
     *
     * @part json
     * @param $jsonType jsonType structure
     * @param null $jsonPath optionally set specific path to structure with JsonPath
     * @see seeResponseMatchesJsonType
     * @version 2.1.3
     */
    public function dontSeeResponseMatchesJsonType($jsonType, $jsonPath = null)
    {
        $jsonArray = new JsonArray($this->connectionModule->_getResponseContent());
        if ($jsonPath) {
            $jsonArray = $jsonArray->filterByJsonPath($jsonPath);
        }

        \PHPUnit\Framework\Assert::assertThat($jsonArray, new JsonTypeConstraint($jsonType, false));
    }

    /**
     * Checks if response is exactly the same as provided.
     *
     * @part json
     * @part xml
     * @param $response
     */
    public function seeResponseEquals($expected)
    {
        $this->assertEquals($expected, $this->connectionModule->_getResponseContent());
    }

    /**
     * Checks response code equals to provided value.
     *
     * ```php
     * <?php
     * $I->seeResponseCodeIs(200);
     *
     * // preferred to use \Codeception\Util\HttpCode
     * $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
     * ```
     *
     * @part json
     * @part xml
     * @param $code
     */
    public function seeResponseCodeIs($code)
    {
        $this->connectionModule->seeResponseCodeIs($code);
    }

    /**
     * Checks that response code is not equal to provided value.
     *
     * ```php
     * <?php
     * $I->dontSeeResponseCodeIs(200);
     *
     * // preferred to use \Codeception\Util\HttpCode
     * $I->dontSeeResponseCodeIs(\Codeception\Util\HttpCode::OK);
     * ```
     *
     * @part json
     * @part xml
     * @param $code
     */
    public function dontSeeResponseCodeIs($code)
    {
        $this->connectionModule->dontSeeResponseCodeIs($code);
    }

    /**
     * Checks that the response code is 2xx
     *
     * @part json
     * @part xml
     */
    public function seeResponseCodeIsSuccessful()
    {
        $this->connectionModule->seeResponseCodeIsSuccessful();
    }

    /**
     * Checks that the response code 3xx
     *
     * @part json
     * @part xml
     */
    public function seeResponseCodeIsRedirection()
    {
        $this->connectionModule->seeResponseCodeIsRedirection();
    }

    /**
     * Checks that the response code is 4xx
     *
     * @part json
     * @part xml
     */
    public function seeResponseCodeIsClientError()
    {
        $this->connectionModule->seeResponseCodeIsClientError();
    }

    /**
     * Checks that the response code is 5xx
     *
     * @part json
     * @part xml
     */
    public function seeResponseCodeIsServerError()
    {
        $this->connectionModule->seeResponseCodeIsServerError();
    }


    /**
     * Checks whether last response was valid XML.
     * This is done with libxml_get_last_error function.
     *
     * @part xml
     */
    public function seeResponseIsXml()
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($this->connectionModule->_getResponseContent());
        $num = "";
        $title = "";
        if ($doc === false) {
            $error = libxml_get_last_error();
            $num = $error->code;
            $title = trim($error->message);
            libxml_clear_errors();
        }
        libxml_use_internal_errors(false);
        \PHPUnit\Framework\Assert::assertNotSame(
            false,
            $doc,
            "xml decoding error #$num with message \"$title\", see http://www.xmlsoft.org/html/libxml-xmlerror.html"
        );
    }

    /**
     * Checks whether XML response matches XPath
     *
     * ```php
     * <?php
     * $I->seeXmlResponseMatchesXpath('//root/user[@id=1]');
     * ```
     * @part xml
     * @param $xpath
     */
    public function seeXmlResponseMatchesXpath($xpath)
    {
        $structure = new XmlStructure($this->connectionModule->_getResponseContent());
        $this->assertTrue($structure->matchesXpath($xpath), 'xpath not matched');
    }

    /**
     * Checks whether XML response does not match XPath
     *
     * ```php
     * <?php
     * $I->dontSeeXmlResponseMatchesXpath('//root/user[@id=1]');
     * ```
     * @part xml
     * @param $xpath
     */
    public function dontSeeXmlResponseMatchesXpath($xpath)
    {
        $structure = new XmlStructure($this->connectionModule->_getResponseContent());
        $this->assertFalse($structure->matchesXpath($xpath), 'accidentally matched xpath');
    }

    /**
     * Finds and returns text contents of element.
     * Element is matched by either CSS or XPath
     *
     * @param $cssOrXPath
     * @return string
     * @part xml
     */
    public function grabTextContentFromXmlElement($cssOrXPath)
    {
        $el = (new XmlStructure($this->connectionModule->_getResponseContent()))->matchElement($cssOrXPath);
        return $el->textContent;
    }

    /**
     * Finds and returns attribute of element.
     * Element is matched by either CSS or XPath
     *
     * @param $cssOrXPath
     * @param $attribute
     * @return string
     * @part xml
     */
    public function grabAttributeFromXmlElement($cssOrXPath, $attribute)
    {
        $el = (new XmlStructure($this->connectionModule->_getResponseContent()))->matchElement($cssOrXPath);
        if (!$el->hasAttribute($attribute)) {
            $this->fail("Attribute not found in element matched by '$cssOrXPath'");
        }
        return $el->getAttribute($attribute);
    }

    /**
     * Checks XML response equals provided XML.
     * Comparison is done by canonicalizing both xml`s.
     *
     * Parameters can be passed either as DOMDocument, DOMNode, XML string, or array (if no attributes).
     *
     * @param $xml
     * @part xml
     */
    public function seeXmlResponseEquals($xml)
    {
        \PHPUnit\Framework\Assert::assertXmlStringEqualsXmlString($this->connectionModule->_getResponseContent(), $xml);
    }


    /**
     * Checks XML response does not equal to provided XML.
     * Comparison is done by canonicalizing both xml`s.
     *
     * Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).
     *
     * @param $xml
     * @part xml
     */
    public function dontSeeXmlResponseEquals($xml)
    {
        \PHPUnit\Framework\Assert::assertXmlStringNotEqualsXmlString(
            $this->connectionModule->_getResponseContent(),
            $xml
        );
    }

    /**
     * Checks XML response includes provided XML.
     * Comparison is done by canonicalizing both xml`s.
     * Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeXmlResponseIncludes("<result>1</result>");
     * ?>
     * ```
     *
     * @param $xml
     * @part xml
     */
    public function seeXmlResponseIncludes($xml)
    {
        $this->assertStringContainsString(
            XmlUtils::toXml($xml)->C14N(),
            XmlUtils::toXml($this->connectionModule->_getResponseContent())->C14N(),
            "found in XML Response"
        );
    }

    /**
     * Checks XML response does not include provided XML.
     * Comparison is done by canonicalizing both xml`s.
     * Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).
     *
     * @param $xml
     * @part xml
     */
    public function dontSeeXmlResponseIncludes($xml)
    {
        $this->assertStringNotContainsString(
            XmlUtils::toXml($xml)->C14N(),
            XmlUtils::toXml($this->connectionModule->_getResponseContent())->C14N(),
            "found in XML Response"
        );
    }

    /**
     * Checks if the hash of a binary response is exactly the same as provided.
     * Parameter can be passed as any hash string supported by hash(), with an
     * optional second parameter to specify the hash type, which defaults to md5.
     *
     * Example: Using md5 hash key
     *
     * ```php
     * <?php
     * $I->seeBinaryResponseEquals("8c90748342f19b195b9c6b4eff742ded");
     * ?>
     * ```
     *
     * Example: Using md5 for a file contents
     *
     * ```php
     * <?php
     * $fileData = file_get_contents("test_file.jpg");
     * $I->seeBinaryResponseEquals(md5($fileData));
     * ?>
     * ```
     * Example: Using sha256 hash
     *
     * ```php
     * <?php
     * $fileData = '/9j/2wBDAAMCAgICAgMCAgIDAwMDBAYEBAQEBAgGBgUGCQgKCgkICQkKDA8MCgsOCwkJDRENDg8QEBEQCgwSExIQEw8QEBD/yQALCAABAAEBAREA/8wABgAQEAX/2gAIAQEAAD8A0s8g/9k='; // very small jpeg
     * $I->seeBinaryResponseEquals(hash("sha256", base64_decode($fileData)), 'sha256');
     * ?>
     * ```
     *
     * @param $hash the hashed data response expected
     * @param $algo the hash algorithm to use. Default md5.
     * @part json
     * @part xml
     */
    public function seeBinaryResponseEquals($hash, $algo = 'md5')
    {
        $responseHash = hash($algo, $this->connectionModule->_getResponseContent());
        $this->assertEquals($hash, $responseHash);
    }

    /**
     * Checks if the hash of a binary response is not the same as provided.
     *
     * ```php
     * <?php
     * $I->dontSeeBinaryResponseEquals("8c90748342f19b195b9c6b4eff742ded");
     * ?>
     * ```
     * Opposite to `seeBinaryResponseEquals`
     *
     * @param $hash the hashed data response expected
     * @param $algo the hash algorithm to use. Default md5.
     * @part json
     * @part xml
     */
    public function dontSeeBinaryResponseEquals($hash, $algo = 'md5')
    {
        $responseHash = hash($algo, $this->connectionModule->_getResponseContent());
        $this->assertNotEquals($hash, $responseHash);
    }

    /**
     * Prevents automatic redirects to be followed by the client
     *
     * ```php
     * <?php
     * $I->stopFollowingRedirects();
     * ```
     *
     * @part xml
     * @part json
     */
    public function stopFollowingRedirects()
    {
        $this->client->followRedirects(false);
    }

    /**
     * Enables automatic redirects to be followed by the client
     *
     * ```php
     * <?php
     * $I->startFollowingRedirects();
     * ```
     *
     * @part xml
     * @part json
     */
    public function startFollowingRedirects()
    {
        $this->client->followRedirects(true);
    }
}
