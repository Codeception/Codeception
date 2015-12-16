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
            return false;
        }
        // if it is not an associative array we do not compare keys
        if ($this->arrayIsSequential($arr1) && $this->arrayIsSequential($arr2)) {
            return $this->sequentialArrayIntersect($arr1, $arr2);
        }
        return $this->associativeArrayIntersect($arr1, $arr2);
    }

    /**
     * This array has sequential keys?
     *
     * @param array $array
     *
     * @return bool
     */
    private function arrayIsSequential(array $array)
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    private function sequentialArrayIntersect(array $arr1, array $arr2)
    {
        $ret = [];
        
        // Do not match the same item of $arr2 against multiple items of $arr1
        $matchedKeys = [];
        foreach ($arr1 as $key1 => $value1) {
            foreach ($arr2 as $key2 => $value2) {
                if (isset($matchedKeys[$key2])) {
                    continue;
                }

                $return = $this->arrayIntersectRecursive($value1, $value2);
                if ($return !== false && $return == $value1) {
                    $ret[$key1] = $return;
                    $matchedKeys[$key2] = true;
                    continue;
                }

                if ($this->isEqualValue($value1, $value2)) {
                    $ret[$key1] = $value1;
                    $matchedKeys[$key2] = true;
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * @param array $arr1
     * @param array $arr2
     *
     * @return array|bool|null
     */
    private function associativeArrayIntersect(array $arr1, array $arr2)
    {
        $commonKeys = array_intersect(array_keys($arr1), array_keys($arr2));

        $ret = [];
        foreach ($commonKeys as $key) {
            $return = $this->arrayIntersectRecursive($arr1[$key], $arr2[$key]);
            if ($return) {
                $ret[$key] = $return;
                continue;
            }
            if ($this->isEqualValue($arr1[$key], $arr2[$key])) {
                $ret[$key] = $arr1[$key];
            }
        }

        if (empty($commonKeys)) {
            foreach ($arr2 as $arr) {
                $return = $this->arrayIntersectRecursive($arr1, $arr);
                if ($return && $return == $arr1) {
                    return $return;
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

    private function isEqualValue($val1, $val2)
    {
        if (is_numeric($val1)) {
            $val1 = (string) $val1;
        }

        if (is_numeric($val2)) {
            $val2 = (string) $val2;
        }

        return $val1 === $val2;
    }
}
