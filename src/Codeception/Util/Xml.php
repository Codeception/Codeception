<?php

declare(strict_types=1);

namespace Codeception\Util;

use DOMDocument;
use DOMNode;
use function is_array;

class Xml
{
    /**
     * @static
     *
     * @param DOMDocument $xml
     * @param DOMNode $domNode
     * @param array $array
     * @return DOMDocument
     */
    public static function arrayToXml(DOMDocument $xml, DOMNode $domNode, array $array = []): DOMDocument
    {
        foreach ($array as $el => $val) {
            if (is_array($val)) {
                self::arrayToXml($xml, $domNode->$el, $val);
            } else {
                $domNode->appendChild($xml->createElement($el, $val));
            }
        }
        return $xml;
    }

    /**
     * @static
     *
     * @param XmlBuilder|DOMDocument $xml
     * @return DOMDocument|DOMNode
     */
    public static function toXml($xml)
    {
        if ($xml instanceof XmlBuilder) {
            return $xml->getDom();
        }
        if ($xml instanceof DOMDocument) {
            return $xml;
        }
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        if ($xml instanceof DOMNode) {
            $xml = $dom->importNode($xml, true);
            $dom->appendChild($xml);
            return $dom;
        }

        if (is_array($xml)) {
            return self::arrayToXml($dom, $dom, $xml);
        }
        if (!empty($xml)) {
            $dom->loadXML($xml);
        }
        return $dom;
    }

    public static function build(): XmlBuilder
    {
        return new XmlBuilder();
    }
}
