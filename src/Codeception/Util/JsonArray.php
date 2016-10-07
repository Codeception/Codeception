<?php
namespace Codeception\Util;

use Flow\JSONPath\JSONPath;
use InvalidArgumentException;
use DOMDocument;

class JsonArray
{
    /**
     * @var array
     */
    protected $jsonArray = [];
    
    /**
     * @var DOMDocument
     */
    protected $jsonXml = null;

    public function __construct($jsonString)
    {
        if (!is_string($jsonString)) {
            throw new InvalidArgumentException('$jsonString param must be a string.');
        }

        $this->jsonArray = json_decode($jsonString, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid json: %s. System message: %s.",
                    $jsonString,
                    json_last_error_msg()
                ),
                json_last_error()
            );
        }
    }

    public function toXml()
    {
        if ($this->jsonXml) {
            return $this->jsonXml;
        }

        $root = 'root';
        $jsonArray = $this->jsonArray;
        if (count($jsonArray) == 1) {
            $root = key($jsonArray);
            $jsonArray = reset($jsonArray);
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $root = $dom->createElement($root);
        $dom->appendChild($root);
        $this->arrayToXml($dom, $root, $jsonArray);
        $this->jsonXml = $dom;
        return $dom;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->jsonArray;
    }

    public function filterByXPath($xpath)
    {
        $path = new \DOMXPath($this->toXml());
        return $path->query($xpath);
    }

    public function filterByJsonPath($jsonPath)
    {
        if (!class_exists('Flow\JSONPath\JSONPath')) {
            throw new \Exception('JSONPath library not installed. Please add `flow/jsonpath` to composer.json');
        }
        return (new JSONPath($this->jsonArray))->find($jsonPath)->data();
    }

    public function getXmlString()
    {
        return $this->toXml()->saveXML();
    }

    public function containsArray(array $needle)
    {
        return (new ArrayContainsComparator($this->jsonArray))->containsArray($needle);
    }

    private function arrayToXml(\DOMDocument $doc, \DOMNode $node, $array)
    {
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $subNode = $doc->createElement($node->nodeName);
                $node->parentNode->appendChild($subNode);
            } else {
                try {
                    $subNode = $doc->createElement($key);
                } catch (\Exception $e) {
                    $key = $this->getValidTagNameForInvalidKey($key);
                    $subNode = $doc->createElement($key);
                }
                $node->appendChild($subNode);
            }
            if (is_array($value)) {
                $this->arrayToXml($doc, $subNode, $value);
            } else {
                $subNode->nodeValue = htmlspecialchars((string)$value);
            }
        }
    }

    private function getValidTagNameForInvalidKey($key)
    {
        static $map = [];
        if (!isset($map[$key])) {
            $tagName = 'invalidTag' . (count($map) + 1);
            $map[$key] = $tagName;
            codecept_debug($tagName . ' is "' . $key . '"');
        }
        return $map[$key];
    }
}
