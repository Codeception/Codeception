<?php
namespace Codeception\Util;

class XmlBuilder
{
    /**
     * @var \DOMDocument
     */
    protected $__dom__;

    /**
     * @var \DOMElement
     */
    protected $__currentNode__;

    public function __construct() {
        $this->__dom__ = new \DOMDocument();
        $this->__currentNode__ = $this->__dom__;
    }

    /**
     * Appends child node
     *
     * @param $tag
     * @return XmlBuilder
     */
    public function __get($tag) {
        $node = $this->__dom__->createElement($tag);
        $this->__currentNode__->appendChild($node);
        $this->__currentNode__ = $node;
        return $this;
    }

    /**
     * @param $val
     * @return XmlBuilder
     */
    public function val($val) {
        $this->__currentNode__->nodeValue = $val;
        return $this;
    }

    /**
     * Sets attribute for current node
     *
     * @param $attr
     * @param $val
     * @return XmlBuilder
     */
    public function attr($attr, $val) {
        $this->__currentNode__->setAttribute($attr, $val);
        return $this;
    }

    /**
     * Traverses to parent
     *
     * @return XmlBuilder
     */
    public function parent() {
        $this->__currentNode__ = $this->__currentNode__->parentNode;
        return $this;
    }


    /**
     * Traverses to parent with $name
     *
     * @param $tag
     * @return XmlBuilder
     * @throws \Exception
     */
    public function parents($tag) {
        $traverseNode = $this->__currentNode__;
        $elFound = false;
        while ($traverseNode->parentNode) {
            $traverseNode = $traverseNode->parentNode;
            if ($traverseNode->tagName == $tag) {
                $this->__currentNode__ = $traverseNode;
                $elFound = true;
                break;
            }
        }
        if (!$elFound) throw new \Exception("Parent $tag not found in XML");
        return $this;
    }

    public function __toString() {
        return $this->__dom__->saveXML();
    }

    /**
     * @return \DOMDocument
     */
    public function getDom() {
        return $this->__dom__;
    }

}
