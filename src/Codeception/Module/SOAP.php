<?php
namespace Codeception\Module;

use Codeception\Lib\Interfaces\API;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Notification;
use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Framework;
use Codeception\Lib\InnerBrowser;
use Codeception\Util\Soap as SoapUtils;
use Codeception\Util\XmlStructure;

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
 * * SOAPAction - replace SOAPAction HTTP header (Set to '' to SOAP 1.2)
 *
 * ## Public Properties
 *
 * * xmlRequest - last SOAP request (DOMDocument)
 * * xmlResponse - last SOAP response (DOMDocument)
 *
 */
class SOAP extends CodeceptionModule implements DependsOnModule, API
{
    protected $config = [
        'schema' => "",
        'schema_url' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'framework_collect_buffer' => true
    ];

    protected $requiredFields = ['endpoint'];

    protected $dependencyMessage = <<<EOF
Example using PhpBrowser as backend for SOAP module.
--
modules:
    enabled:
        - SOAP:
            depends: PhpBrowser
--
Framework modules can be used as well for functional testing of SOAP API.
EOF;

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
    protected $xmlStructure = null;

    /**
     * @var InnerBrowser
     */
    protected $connectionModule;

    public function _before(TestInterface $test)
    {
        $this->client = &$this->connectionModule->client;
        $this->buildRequest();
        $this->xmlResponse = null;
        $this->xmlStructure = null;
    }

    protected function onReconfigure()
    {
        $this->buildRequest();
        $this->xmlResponse = null;
        $this->xmlStructure = null;
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

    private function getXmlResponse()
    {
        if (!$this->xmlResponse) {
            throw new ModuleException($this, "No XML response, use `\$I->sendSoapRequest` to receive it");
        }
        return $this->xmlResponse;
    }

    private function getXmlStructure()
    {
        if (!$this->xmlStructure) {
            $this->xmlStructure = new XmlStructure($this->getXmlResponse());
        }
        return $this->xmlStructure;
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
     * $I->sendSoapRequest('UpdateUser', '<user><id>1</id><name>notdavert</name></user>');
     * $I->sendSoapRequest('UpdateUser', \Codeception\Utils\Soap::request()->user
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

        if ($this->isFunctional && $this->config['framework_collect_buffer']) {
            $response = $this->processInternalRequest($action, $req);
        } else {
            $response = $this->processExternalRequest($action, $req);
        }

        $this->debugSection("Response", $response);
        $this->xmlResponse = SoapUtils::toXml($response);
        $this->xmlStructure = null;
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
        $this->assertEquals($xml->C14N(), $this->getXmlResponse()->C14N());
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
        $this->assertContains($xml, $this->getXmlResponse()->C14N(), "found in XML Response");
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
        \PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlString($xml->C14N(), $this->getXmlResponse()->C14N());
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
        $this->assertNotContains($xml, $this->getXmlResponse()->C14N(), "found in XML Response");
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
        $this->assertTrue((bool)$this->getXmlStructure()->matchXmlStructure($xml), "this structure is in response");
    }

    /**
     * Opposite to `seeSoapResponseContainsStructure`
     * @param $xml
     */
    public function dontSeeSoapResponseContainsStructure($xml)
    {
        $xml = SoapUtils::toXml($xml);
        $this->debugSection("Structure", $xml->saveXML());
        $this->assertFalse((bool)$this->getXmlStructure()->matchXmlStructure($xml), "this structure is in response");
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
        $this->assertTrue($this->getXmlStructure()->matchesXpath($xpath));
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
        $this->assertFalse($this->getXmlStructure()->matchesXpath($xpath));
    }


    /**
     * Checks response code from server.
     *
     * @param $code
     */
    public function seeSoapResponseCodeIs($code)
    {
        $this->assertEquals(
            $code,
            $this->client->getInternalResponse()->getStatus(),
            "soap response code matches expected"
        );
    }

    /**
     * @deprecated use seeSoapResponseCodeIs instead
     */
    public function seeResponseCodeIs($code)
    {
        Notification::deprecate('SOAP::seeResponseCodeIs deprecated in favor of seeSoapResponseCodeIs', 'SOAP Module');
        $this->seeSoapResponseCodeIs($code);
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
        $el = $this->getXmlStructure()->matchElement($cssOrXPath);
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
        $el = $this->getXmlStructure()->matchElement($cssOrXPath);
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
            [],
            [],
            [
                'HTTP_Content-Type' => 'text/xml; charset=UTF-8',
                'HTTP_Content-Length' => strlen($body),
                'HTTP_SOAPAction' => isset($this->config['SOAPAction']) ? $this->config['SOAPAction'] : $action
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
