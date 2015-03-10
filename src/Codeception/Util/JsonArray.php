<?php
namespace Codeception\Util;

use Flow\JSONPath\JSONPath;

class JsonArray
{
    protected $jsonArray;
    protected $jsonXml;

    public function __construct($jsonString)
    {
        $this->jsonArray = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Invalid json: $jsonString");
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

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $root = $dom->createElement($root);
        $dom->appendChild($root);
        $this->arrayToXml($dom, $root, $jsonArray);
        $this->jsonXml = $dom;
        return $dom;
    }

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
        return $needle == $this->arrayIntersectRecursive($needle, $this->jsonArray);
    }

    /**
     * @author nleippe@integr8ted.com
     * @author tiger.seo@gmail.com
     * @link http://www.php.net/manual/en/function.array-intersect-assoc.php#39822
     *
     * @param mixed $arr1
     * @param mixed $arr2
     *
     * @return array|bool
     */
    private function arrayIntersectRecursive($arr1, $arr2)
    {
        if (!is_array($arr1) || !is_array($arr2)) {
            return null;
        }
        // if it is not an associative array we do not compare keys
        if ($this->arrayIsSequential($arr1) and $this->arrayIsSequential($arr2)) {
            return $this->sequentialArrayIntersect($arr1, $arr2);
        }

        return $this->associativeArrayIntersect($arr1, $arr2);
    }

    /**
     * @param $arr1
     * @return bool
     */
    private function arrayIsSequential($arr1)
    {
        return array_keys($arr1) === range(0, count($arr1) - 1);
    }

    /**
     * @param $arr1
     * @param $arr2
     * @return array
     */
    private function sequentialArrayIntersect($arr1, $arr2)
    {
        $ret = [];
        foreach ($arr1 as $key1 => $value1) {
            foreach ($arr2 as $key2 => $value2) {
                $_return = $this->arrayIntersectRecursive($value1, $value2);
                if ($_return) {
                    $ret[$key1] = $_return;
                    continue;
                }
                if ($value1 === $value2) {
                    $ret[$key1] = $value1;
                }
            }
        }
        return $ret;
    }

    /**
     * @param $arr1
     * @param $arr2
     * @return array|bool|null
     */
    private function associativeArrayIntersect($arr1, $arr2)
    {
        $commonKeys = array_intersect(array_keys($arr1), array_keys($arr2));

        $ret = [];
        foreach ($commonKeys as $key) {
            $_return = $this->arrayIntersectRecursive($arr1[$key], $arr2[$key]);
            if ($_return) {
                $ret[$key] = $_return;
                continue;
            }
            if ($arr1[$key] === $arr2[$key]) {
                $ret[$key] = $arr1[$key];
            }
        }

        if (empty($commonKeys)) {
            foreach ($arr2 as $arr) {
                $_return = $this->arrayIntersectRecursive($arr1, $arr);
                if ($_return && $_return == $arr1) {
                    return $_return;
                }
            }
        }

        if (count($ret) < min(count($arr1), count($arr2))) {
            return null;
        }

        return $ret;
    }

    private function arrayToXml(\DOMDocument $doc, \DOMNode $node, $array)
    {
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $subNode = $doc->createElement($node->nodeName);
                $node->parentNode->appendChild($subNode);
            } else {
                $subNode = $doc->createElement($key);
                $node->appendChild($subNode);
            }
            if (is_array($value)) {
                $this->arrayToXml($doc, $subNode, $value);
            } else {
                $subNode->nodeValue = (string)$value;
            }
        }
    }
}