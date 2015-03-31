<?php

use Codeception\Util\Stub as Stub;
use Codeception\Util\Soap as SoapUtil;

class SoapTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Codeception\Module\Soap
     */
    protected $module = null;

    protected $layout;

    public function setUp() {
        $this->module = new \Codeception\Module\SOAP(make_container());
        $this->module->_setConfig(array('schema' => 'http://www.w3.org/2001/xml.xsd', 'endpoint' => 'http://codeception.com/api/wsdl'));
        $this->layout = \Codeception\Configuration::dataDir().'/xml/layout.xml';
        $this->module->isFunctional = true;
        $this->module->_before(Stub::makeEmpty('\Codeception\TestCase\Cept'));
        $this->module->client = Stub::makeEmpty('\Codeception\Lib\Connector\Universal');
    }
    
    public function testXmlIsBuilt() {
        $dom = new \DOMDocument();
        $dom->load($this->layout);
        $this->assertEqualXMLStructure($this->module->xmlRequest->documentElement, $dom->documentElement);
        $this->assertXmlStringEqualsXmlString($dom->saveXML(), $this->module->xmlRequest->saveXML());
    }
    
    public function testBuildHeaders() {
        $this->module->haveSoapHeader('AuthHeader', array('username' => 'davert', 'password' => '123456'));
        $dom = new \DOMDocument();
        $dom->load($this->layout);
        $header = $dom->createElement('AuthHeader');
        $header->appendChild($dom->createElement('username','davert'));
        $header->appendChild($dom->createElement('password','123456'));
        $dom->documentElement->getElementsByTagName('Header')->item(0)->appendChild($header);
        $this->assertEqualXMLStructure($this->module->xmlRequest->documentElement, $dom->documentElement);
    }

    public function testBuildRequest()
    {
        $this->module->sendSoapRequest('KillHumans', "<item><id>1</id><subitem>2</subitem></item>");
        $this->assertNotNull($this->module->xmlRequest);
        $dom = new \DOMDocument();
        $dom->load($this->layout);
        $body = $dom->createElement('item');
        $body->appendChild($dom->createElement('id',1));
        $body->appendChild($dom->createElement('subitem',2));
        $request = $dom->createElement('ns:KillHumans');
        $request->appendChild($body);
        $dom->documentElement->getElementsByTagName('Body')->item(0)->appendChild($request);
        $this->assertEqualXMLStructure($this->module->xmlRequest->documentElement, $dom->documentElement);
    }

    public function testBuildRequestWithDomNode() {
        $dom = new \DOMDocument();
        $dom->load($this->layout);
        $body = $dom->createElement('item');
        $body->appendChild($dom->createElement('id',1));
        $body->appendChild($dom->createElement('subitem',2));
        $request = $dom->createElement('ns:KillHumans');
        $request->appendChild($body);
        $dom->documentElement->getElementsByTagName('Body')->item(0)->appendChild($request);

        $this->module->sendSoapRequest('KillHumans', $body);
        $this->assertEqualXMLStructure($this->module->xmlRequest->documentElement, $dom->documentElement);
    }
    
    public function testSeeXmlIncludes() {
        $dom = new DOMDocument();
        $this->module->xmlResponse = $dom;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML('<?xml version="1.0" encoding="UTF-8"?>    <doc> <a a2="2" a1="1" >123</a>  </doc>');
        $this->module->seeSoapResponseIncludes('<a    a2="2"      a1="1" >123</a>');
    }

    public function testSeeXmlContainsXPath() {
        $dom = new DOMDocument();
        $this->module->xmlResponse = $dom;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML('<?xml version="1.0" encoding="UTF-8"?>    <doc> <a a2="2" a1="1" >123</a>  </doc>');
        $this->module->seeSoapResponseContainsXPath('//doc/a[@a2=2 and @a1=1]');
    }

    public function testSeeXmlNotContainsXPath() {
        $dom = new DOMDocument();
        $this->module->xmlResponse = $dom;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML('<?xml version="1.0" encoding="UTF-8"?>    <doc> <a a2="2" a1="1" >123</a>  </doc>');
        $this->module->dontSeeSoapResponseContainsXPath('//doc/a[@a2=2 and @a31]');
    }


    public function testSeeXmlEquals() {
        $dom = new DOMDocument();
        $this->module->xmlResponse = $dom;
        $xml = '<?xml version="1.0" encoding="UTF-8"?> <doc> <a a2="2" a1="1" >123</a>  </doc>';
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);
        $this->module->seeSoapResponseEquals($xml);
    }

    public function testSeeXmlIncludesWithBuilder() {
        $dom = new DOMDocument();
        $this->module->xmlResponse = $dom;
        $dom->loadXML('<?xml version="1.0" encoding="UTF-8"?>'."\n".'  <doc><a    a2="2" a1="1"  >123</a></doc>');
        $xml = SoapUtil::request()->doc->a
                ->attr('a2','2')
                ->attr('a1','1')
                ->val('123');
        $this->module->seeSoapResponseIncludes($xml);
    }
    
    public function testGrabTextFrom() {
        $dom = new DOMDocument();
        $this->module->xmlResponse = $dom;
        $dom->loadXML('<?xml version="1.0" encoding="UTF-8"?><doc><node>123</node></doc>');
        $res = $this->module->grabTextContentFrom('doc node');
        $this->assertEquals('123', $res);
        $res = $this->module->grabTextContentFrom('descendant-or-self::doc/descendant::node');
        $this->assertEquals('123', $res);
    }

}
