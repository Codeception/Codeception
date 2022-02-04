<?php

declare(strict_types=1);

namespace Codeception\Util;

use Codeception\Exception\ElementNotFound;
use Codeception\Exception\MalformedLocatorException;
use Codeception\Util\Soap as SoapXmlUtil;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Symfony\Component\CssSelector\CssSelectorConverter;

class XmlStructure
{
    protected DOMDocument|DOMNode $xml;

    public function __construct($xml)
    {
        $this->xml = SoapXmlUtil::toXml($xml);
    }

    public function matchesXpath(string $xpath): bool
    {
        $domXpath = new DOMXPath($this->xml);
        $res = $domXpath->query($xpath);
        if ($res === false) {
            throw new MalformedLocatorException($xpath);
        }
        return $res->length > 0;
    }

    public function matchElement(string $cssOrXPath): ?DOMNode
    {
        $domXpath = new DOMXpath($this->xml);
        $selector = (new CssSelectorConverter())->toXPath($cssOrXPath);
        $els = $domXpath->query($selector);
        if ($els) {
            return $els->item(0);
        }
        $els = $domXpath->query($cssOrXPath);
        if ($els->length !== 0) {
            return $els->item(0);
        }
        throw new ElementNotFound($cssOrXPath);
    }

    public function matchXmlStructure($xml): bool
    {
        $xml = SoapXmlUtil::toXml($xml);
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

    protected function matchForNode($schema, $xml): bool
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
