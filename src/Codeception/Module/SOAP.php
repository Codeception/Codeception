<?php
namespace Codeception\Module;

/**
 * Module for testing SOAP WSDL web services.
 * Send requests and check if response matches the pattern.
 *
 * This module can be used either with frameworks or PHPBrowser.
 * It tries to guess the framework is is attached to.
 * If a endpoint is a full url then it uses PHPBrowser.
 *
 * ### Using Inside Framework
 *
 * Please note, that PHP SoapServer::handle method sends additional headers.
 * This may trigger warning: "Cannot modify header information"
 * If you use PHP SoapServer with framework, try to block call to this method in testing environment.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Configuration
 *
 * * endpoint *required* - soap wsdl endpoint
 *
 * ## Public Properties
 *
 * * request - last soap request (DOMDocument)
 * * response - last soap response (DOMDocument)
 *
 */

use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Framework;
use Codeception\Lib\InnerBrowser;
use Codeception\Util\Soap as SoapUtils;
use Codeception\Util\XmlStructure;

class SOAP extends \Codeception\Module
{

    protected $dependencyMessage = <<<EOF
Example using PhpBrowser as backend for SOAP module.
--
modules:
    enabled: [SOAP, ApiHelper]
    depends:
        SOAP: PhpBrowser
--
Framework modules can be used as well for functional testing of SOAP API.
EOF;


    protected $config = ['schema' => "", 'schema_url' => 'http://schemas.xmlsoap.org/soap/envelope/'];
    protected $requiredFields = ['endpoint'];
    /**
     * @var \Symfony\Component\BrowserKit\Client
     */
    public $client = null;
    public $isFunctional = false;

    /**
     * @var \DOMDocument
     */
    public $xmlRequest = null;
    /**
     * @var \DOMDocument
     */
    public $xmlResponse = null;

    /**
     * @var XmlStructure
     */
    protected $xmlStructure;

    /**
     * @var InnerBrowser
     */
    protected $connectionModule;

    public function _before(\Codeception\TestCase $test)
    {
        $this->client = &$this->connectionModule->client;
        $this->buildRequest();
        $this->xmlResponse = null;
    }

    public function _depends()
    {
        return ['Codeception\Lib\InnerBrowser' => $this->dependencyMessage];
    }

    public function _inject(InnerBrowser $connectionModule)
    {
        $this->connectionModule = $connectionModule;
        if ($connectionModule instanceof Framework) {
            $this->isFunctional = true;
        }
    }
    
    private function getClient()
    {
        if (!$this->client) {
            throw new ModuleRequireException($this, "Connection client is not available.");
        }
        return $this->client;
    }


    /**
     * Prepare SOAP header.
     * Receives header name and parameters as array.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->haveSoapHeader('AuthHeader', array('username' => 'davert', 'password' => '123345'));
     * ```
     *
     * Will produce header:
     *
     * ```
     *    <soapenv:Header>
     *      <SessionHeader>
     *      <AuthHeader>
     *          <username>davert</username>
     *          <password>12345</password>
     *      </AuthHeader>
     *   </soapenv:Header>
     * ```
     *
     * @param $header
     * @param array $params
     */
    public function haveSoapHeader($header, $params = [])
    {
        $soap_schema_url = $this->config['schema_url'];
        $xml = $this->xmlRequest;
        $xmlHeader = $xml->documentElement->getElementsByTagNameNS($soap_schema_url, 'Header')->item(0);
        $headerEl = $xml->createElement($header);
        SoapUtils::arrayToXml($xml, $headerEl, $params);
        $xmlHeader->appendChild($headerEl);
    }

    /**
     * Submits request to endpoint.
     *
     * Requires of api function name and parameters.
     * Parameters can be passed either as DOMDocument, DOMNode, XML string, or array (if no attributes).
     *
     * You are allowed to execute as much requests as you need inside test.
     *
     * Example:
     *
     * ``` php
     * $I->sendRequest('UpdateUser', '<user><id>1</id><name>notdavert</name></user>');
     * $I->sendRequest('UpdateUser', \Codeception\Utils\Soap::request()->user
     *   ->id->val(1)->parent()
     *   ->name->val('notdavert');
     * ```
     *
     * @param $request
     * @param $body
     */
    public function sendSoapRequest($action, $body = "")
    {
        $soap_schema_url = $this->config['schema_url'];
        $xml = $this->xmlRequest;
        $call = $xml->createElement('ns:' . $action);
        if ($body) {
            $bodyXml = SoapUtils::toXml($body);
            if ($bodyXml->hasChildNodes()) {
                foreach ($bodyXml->childNodes as $bodyChildNode) {
                    $bodyNode = $xml->importNode($bodyChildNode, true);
                    $call->appendChild($bodyNode);
                }
            }
        }

        $xmlBody = $xml->getElementsByTagNameNS($soap_schema_url, 'Body')->item(0);

        // cleanup if body already set
        foreach ($xmlBody->childNodes as $node) {
            $xmlBody->removeChild($node);
        }

        $xmlBody->appendChild($call);
        $this->debugSection("Request", $req = $xml->C14N());

        if ($this->isFunctional) {
            $response = $this->processInternalRequest($action, $req);
        } else {
            $response = $this->processExternalRequest($action, $req);
        }

        $this->debugSection("Response", $response);
        $this->xmlResponse = SoapUtils::toXml($response);
        $this->xmlStructure = new XmlStructure($this->xmlResponse);
    }

    /**
     * Checks XML response equals provided XML.
     * Comparison is done by canonicalizing both xml`s.
     *
     * Parameters can be passed either as DOMDocument, DOMNode, XML string, or array (if no attributes).
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeSoapResponseEquals("<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope><SOAP-ENV:Body><result>1</result></SOAP-ENV:Envelope>");
     *
     * $dom = new \DOMDocument();
     * $dom->load($file);
     * $I->seeSoapRequestIncludes($dom);
     *
     * ```
     *
     * @param $xml
     */
    public function seeSoapResponseEquals($xml)
    {
        $xml = SoapUtils::toXml($xml);
        $this->assertEquals($this->xmlResponse->C14N(), $xml->C14N());
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
     * $I->seeSoapResponseIncludes("<result>1</result>");
     * $I->seeSoapRequestIncludes(\Codeception\Utils\Soap::response()->result->val(1));
     *
     * $dom = new \DDOMDocument();
     * $dom->load('template.xml');
     * $I->seeSoapRequestIncludes($dom);
     * ?>
     * ```
     *
     * @param $xml
     */
    public function seeSoapResponseIncludes($xml)
    {
        $xml = $this->canonicalize($xml);
        $this->assertContains($xml, $this->xmlResponse->C14N(), "found in XML Response");
    }


    /**
     * Checks XML response equals provided XML.
     * Comparison is done by canonicalizing both xml`s.
     *
     * Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).
     *
     * @param $xml
     */
    public function dontSeeSoapResponseEquals($xml)
    {
        $xml = SoapUtils::toXml($xml);
        \PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlString($this->xmlResponse->C14N(), $xml->C14N());
    }


    /**
     * Checks XML response does not include provided XML.
     * Comparison is done by canonicalizing both xml`s.
     * Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).
     *
     * @param $xml
     */
    public function dontSeeSoapResponseIncludes($xml)
    {
        $xml = $this->canonicalize($xml);
        $this->assertNotContains($xml, $this->xmlResponse->C14N(), "found in XML Response");
    }

    /**
     * Checks XML response contains provided structure.
     * Response elements will be compared with XML provided.
     * Only nodeNames are checked to see elements match.
     *
     * Example:
     *
     * ``` php
     * <?php
     *
     * $I->seeResponseContains("<user><query>CreateUser<name>Davert</davert></user>");
     * $I->seeSoapResponseContainsStructure("<query><name></name></query>");
     * ?>
     * ```
     *
     * Use this method to check XML of valid structure is returned.
     * This method does not use schema for validation.
     * This method does not require path from root to match the structure.
     *
     * @param $xml
     */
    public function seeSoapResponseContainsStructure($xml)
    {
        $xml = SoapUtils::toXml($xml);
        $this->debugSection("Structure", $xml->saveXML());
        $this->assertTrue((bool)$this->xmlValidator->matchXmlStructure($xml), "this structure is in response");
    }

    /**
     * Opposite to `seeSoapResponseContainsStructure`
     * @param $xml
     */
    public function dontSeeSoapResponseContainsStructure($xml)
    {
        $xml = SoapUtils::toXml($xml);
        $this->debugSection("Structure", $xml->saveXML());
        $this->assertFalse((bool)$this->xmlValidator->matchXmlStructure($xml), "this structure is in response");
    }

    /**
     * Checks XML response with XPath locator
     *
     * ``` php
     * <?php
     * $I->seeSoapResponseContainsXPath('//root/user[@id=1]');
     * ?>
     * ```
     *
     * @param $xpath
     */
    public function seeSoapResponseContainsXPath($xpath)
    {
        $this->assertTrue($this->xmlValidator->matchesXpath($xpath));
    }

    /**
     * Checks XML response doesn't contain XPath locator
     *
     * ``` php
     * <?php
     * $I->dontSeeSoapResponseContainsXPath('//root/user[@id=1]');
     * ?>
     * ```
     *
     * @param $xpath
     */
    public function dontSeeSoapResponseContainsXPath($xpath)
    {
        $this->assertFalse($this->xmlValidator->matchesXpath($xpath));
    }


    /**
     * Checks response code from server.
     *
     * @param $code
     */
    public function seeResponseCodeIs($code)
    {
        $this->assertEquals($code, $this->client->getInternalResponse()->getStatus(), "soap response code matches expected");
    }

    /**
     * Finds and returns text contents of element.
     * Element is matched by either CSS or XPath
     *
     * @version 1.1
     * @param $cssOrXPath
     * @return string
     */
    public function grabTextContentFrom($cssOrXPath)
    {
        $el = $this->xmlValidator->matchElement($cssOrXPath);
        return $el->textContent;
    }

    /**
     * Finds and returns attribute of element.
     * Element is matched by either CSS or XPath
     *
     * @version 1.1
     * @param $cssOrXPath
     * @param $attribute
     * @return string
     */
    public function grabAttributeFrom($cssOrXPath, $attribute)
    {
        $el = $this->xmlValidator->matchElement($cssOrXPath);
        if (!$el->hasAttribute($attribute)) {
            $this->fail("Attribute not found in element matched by '$cssOrXPath'");
        }
        return $el->getAttribute($attribute);
    }

    protected function getSchema()
    {
        return $this->config['schema'];
    }

    protected function canonicalize($xml)
    {
        return SoapUtils::toXml($xml)->C14N();
    }

    /**
     * @return \DOMDocument
     */
    protected function buildRequest()
    {
        $soap_schema_url = $this->config['schema_url'];
        $xml = new \DOMDocument();
        $root = $xml->createElement('soapenv:Envelope');
        $xml->appendChild($root);
        $root->setAttribute('xmlns:ns', $this->getSchema());
        $root->setAttribute('xmlns:soapenv', $soap_schema_url);
        $body = $xml->createElementNS($soap_schema_url, 'soapenv:Body');
        $header = $xml->createElementNS($soap_schema_url, 'soapenv:Header');
        $root->appendChild($header);
        $root->appendChild($body);
        $this->xmlRequest = $xml;
        return $xml;
    }

    protected function processRequest($action, $body)
    {
        $this->getClient()->request(
            'POST',
            $this->config['endpoint'],
            [], [],
            [
                "HTTP_Content-Type"   => "text/xml; charset=UTF-8",
                'HTTP_Content-Length' => strlen($body),
                'HTTP_SOAPAction'     => $action
            ],
            $body
        );
    }

    protected function processInternalRequest($action, $body)
    {
        ob_start();
        try {
            $this->getClient()->setServerParameter('HTTP_HOST', 'localhost');
            $this->processRequest($action, $body);
        } catch (\ErrorException $e) {
            // Zend_Soap outputs warning as an exception
            if (strpos($e->getMessage(), 'Warning: Cannot modify header information') === false) {
                ob_end_clean();
                throw $e;
            }
        }
        $response = ob_get_contents();
        ob_end_clean();
        return $response;
    }

    protected function processExternalRequest($action, $body)
    {
        $this->processRequest($action, $body);
        return $this->client->getInternalResponse()->getContent();
    }

}
