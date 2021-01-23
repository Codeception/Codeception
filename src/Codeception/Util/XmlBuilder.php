<?php

declare(strict_types=1);

namespace Codeception\Util;

use DOMDocument;
use DOMElement;
use Exception;

/**
 * That's a pretty simple yet powerful class to build XML structures in jQuery-like style.
 * With no XML line actually written!
 * Uses DOM extension to manipulate XML data.
 *
 *
 * ```php
 * <?php
 * $xml = new \Codeception\Util\XmlBuilder();
 * $xml->users
 *    ->user
 *        ->val(1)
 *        ->email
 *            ->val('davert@mail.ua')
 *            ->attr('valid','true')
 *            ->parent()
 *        ->cart
 *            ->attr('empty','false')
 *            ->items
 *                ->item
 *                    ->val('useful item');
 *                ->parents('user')
 *        ->active
 *            ->val(1);
 * echo $xml;
 * ```
 *
 * This will produce this XML
 *
 * ```xml
 * <?xml version="1.0"?>
 * <users>
 *    <user>
 *        1
 *        <email valid="true">davert@mail.ua</email>
 *        <cart empty="false">
 *            <items>
 *                <item>useful item</item>
 *            </items>
 *        </cart>
 *        <active>1</active>
 *    </user>
 * </users>
 * ```
 *
 * ### Usage
 *
 * Builder uses chained calls. So each call to builder returns a builder object.
 * Except for `getDom` and `__toString` methods.
 *
 *  * `$xml->node` - create new xml node and go inside of it.
 *  * `$xml->node->val('value')` - sets the inner value of node
 *  * `$xml->attr('name','value')` - set the attribute of node
 *  * `$xml->parent()` - go back to parent node.
 *  * `$xml->parents('user')` - go back through all parents to `user` node.
 *
 * Export:
 *
 *  * `$xml->getDom` - get a DOMDocument object
 *  * `$xml->__toString` - get a string representation of XML.
 *
 * [Source code](https://github.com/Codeception/Codeception/blob/4.0/src/Codeception/Util/XmlBuilder.php)
 */
class XmlBuilder
{
    /**
     * @var DOMDocument
     */
    protected $__dom__;

    /**
     * @var DOMElement
     */
    protected $__currentNode__;


    public function __construct()
    {
        $this->__dom__ = new DOMDocument();
        $this->__currentNode__ = $this->__dom__;
    }

    /**
     * Appends child node
     */
    public function __get(string $tag): XmlBuilder
    {
        $domElement = $this->__dom__->createElement($tag);
        $this->__currentNode__->appendChild($domElement);
        $this->__currentNode__ = $domElement;
        return $this;
    }

    public function val($val): self
    {
        $this->__currentNode__->nodeValue = $val;
    }

    /**
     * Sets attribute for current node
     */
    public function attr(string $attr, string $val): self
    {
        $this->__currentNode__->setAttribute($attr, $val);
        return $this;
    }

    /**
     * Traverses to parent
     */
    public function parent(): self
    {
        $this->__currentNode__ = $this->__currentNode__->parentNode;
        return $this;
    }

    /**
     * Traverses to parent with $name
     *
     * @param $tag
     * @return XmlBuilder
     * @throws Exception
     */
    public function parents($tag): self
    {
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

        if (!$elFound) {
            throw new Exception("Parent {$tag} not found in XML");
        }

        return $this;
    }

    public function __toString()
    {
        return $this->__dom__->saveXML();
    }

    public function getDom(): DOMDocument
    {
        return $this->__dom__;
    }
}
