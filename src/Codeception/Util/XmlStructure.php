<?php
namespace Codeception\Util;

use Codeception\Exception\ElementNotFound;
use Codeception\Exception\MalformedLocatorException;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\CssSelector\Exception\ParseException;
use Codeception\Util\Soap as XmlUtils;

class XmlStructure
{
    /**
     * @var \DOMDocument|\DOMNode
     */
    protected $xml;
    
    public function __construct($xml)
    {
        $this->xml = XmlUtils::toXml($xml);
    }

    public function matchesXpath($xpath)
    {
        $path = new \DOMXPath($this->xml);
        $res = $path->query($xpath);
        if ($res === false) {
            throw new MalformedLocatorException($xpath);
        }
        return $res->length > 0;
    }

    /**
     * @param $cssOrXPath
     * @return \DOMElement
     */
    public function matchElement($cssOrXPath)
    {
        $xpath = new \DOMXpath($this->xml);
        try {
            $selector = (new CssSelectorConverter())->toXPath($cssOrXPath);
            $els = $xpath->query($selector);
            if ($els) {
                return $els->item(0);
            }
        } catch (ParseException $e) {
        }
        $els = $xpath->query($cssOrXPath);
        if ($els) {
            return $els->item(0);
        }
        throw new ElementNotFound($cssOrXPath);
    }
    /**

     * @param $xml
     * @return bool
     */
    public function matchXmlStructure($xml)
    {
        $xml = XmlUtils::toXml($xml);
        $root = $xml->firstChild;
        $els = $this->xml->getElementsByTagName($root->nodeName);
        if (empty($els)) {
            throw new ElementNotFound($root->nodeName, 'Element');
        }

        $matches = false;
        foreach ($els as $node) {
            $matches |= $this->matchForNode($root, $node);
        }
        return $matches;
    }

    protected function matchForNode($schema, $xml)
    {
        foreach ($schema->childNodes as $node1) {
            $matched = false;
            foreach ($xml->childNodes as $node2) {
                if ($node1->nodeName == $node2->nodeName) {
                    $matched = $this->matchForNode($node1, $node2);
                    if ($matched) {
                        break;
                    }
                }
            }
            if (!$matched) {
                return false;
            }
        }
        return true;
    }
}
