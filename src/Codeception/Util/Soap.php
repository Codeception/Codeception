<?php

namespace Codeception\Util;

class Soap
{
    /**
     * @static
     *
     * @param \DOMDocument $xml
     * @param \DOMNode     $node
     * @param array       $array
     *
     * @return \DOMDocument
     */
    public static function arrayToXml(\DOMDocument $xml, \DOMNode $node, $array = array())
    {
        foreach ($array as $el => $val) {
            if (is_array($val)) {
                self::arrayToXml($xml, $node->$el, $val);
            } else {
                $node->appendChild($xml->createElement($el, $val));
            }
        }
        return $xml;
    }

    /**
     * @static
     *
     * @param $xml
     *
     * @return \DOMDocument|\DOMNode
     */
    public static function toXml($xml)
    {
        if ($xml instanceof XmlBuilder) {
            return $xml->getDom();
        }
        if ($xml instanceof \DOMDocument) {
            return $xml;
        }
        $dom                     = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        if ($xml instanceof \DOMNode) {
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

    public static function request()
    {
        return new XmlBuilder();
    }

    public static function response()
    {
        return new XmlBuilder();
    }
}
