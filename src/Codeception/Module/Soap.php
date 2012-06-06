<?php
/**
 * Module for testing SOAP WSDL web services.
 * Send requests and check if response matches the pattern.
 *
 * ## Configuration
 *
 * * endpoint *required* - soap wsdl endpoint
 *
 * ## Public Properties
 *
 * * request - last soap request
 * * response - last soap response
 *
 */

namespace Codeception\Module;

use Codeception\Util\Soap as SoapUtils;

class Soap extends \Codeception\Module
{

    protected $config = array('schema' => "", 'schema_url' => 'http://schemas.xmlsoap.org/soap/envelope/');
    protected $requiredFields = array('endpoint');
    /**
     * @var \Symfony\Component\BrowserKit\Client
     */
    public $client = null;

    /**
     * @var \DOMDocument
     */
    public $xmlRequest = null;
    /**
     * @var \DOMDocument
     */
    public $xmlResponse = null;

    public function _before(\Codeception\TestCase $test)
    {
        if (!$this->client) {
            if (!strpos($this->config['endpoint'], '://')) {
                // not valid url
                foreach ($this->getModules() as $module) {
                    if ($module instanceof \Codeception\Util\Framework) {
                        $this->client = $module->client;
                        break;
                    }
                }
            } else {
                if (!$this->hasModule('PhpBrowser'))
                    throw new \Codeception\Exception\ModuleConfig(__CLASS__, "For Soap testing via HTTP please enable PhpBrowser module");
                $this->client = $this->getModule('PhpBrowser')->session->getDriver()->getClient();
            }
            if (!$this->client) throw new \Codeception\Exception\ModuleConfig(__CLASS__, "Client for SOAP requests not initialized.\nProvide either PhpBrowser module, or Framework module which shares FrameworkInterface");
        }

        $this->buildRequest();
        $this->xmlResponse = null;
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
    public function haveSoapHeader($header, $params = array())
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
     * Example:
     *
     * ``` php
     * $I->sendRequest('UpdateUser', '<user><id>1</id><name>notdavert</name></user>');
     * ```
     *
     * @param $request
     * @param $body
     */
    public function sendSoapRequest($request, $body)
    {

        $soap_schema_url = $this->config['schema_url'];
        $xml = $this->xmlRequest;
        $call = $xml->createElement('ns:' . $request);
        $xmlBody = $xml->getElementsByTagNameNS($soap_schema_url, 'Body')->item(0);

        $bodyXml = SoapUtils::toXml($body);

        $bodyNode = $xml->importNode($bodyXml->documentElement, true);
        $call->appendChild($bodyNode);
        $xmlBody->appendChild($call);
        $this->client->request('POST', $this->config['endpoint'], array(), array(), array(), $xml->saveXML());
    }

    /**
     * Checks XML response equals provided XML.
     * Comparison is done by canonicalizing both xml`s.
     *
     * Parameters can be passed either as DOMDocument, DOMNode, XML string, or array (if no attributes).
     *
     * @param $xml
     */
    public function seeSoapResponseEquals($xml)
    {
        $xml = SoapUtils::toXml($xml);
        \PHPUnit_Framework_Assert::assertEquals($this->xmlResponse->C14N(), $xml->C14N());
    }

    /**
     * Checks XML response includes provided XML.
     * Comparison is done by canonicalizing both xml`s.
     * Parameter can be passed either as XmlBuilder, DOMDocument, DOMNode, XML string, or array (if no attributes).
     *
     * @param $xml
     */
    public function seeSoapResponseIncludes($xml)
    {
        $xml = $this->canonicalize($xml);
        \PHPUnit_Framework_Assert::assertContains($xml, $this->xmlResponse->C14N(), "found in XML Response");
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
        \PHPUnit_Framework_Assert::assertNotContains($xml, $this->xmlResponse->C14N(), "found in XML Response");
    }

    protected function getSchema()
    {
        return $this->config['schema'];
    }

    protected function canonicalize($xml)
    {
        $xml = SoapUtils::toXml($xml)->C14N();
        $this->debug($xml);
        return $xml;
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
}
