<?php

namespace Behat\Mink\Element;

use Behat\Mink\Session,
    Behat\Mink\Driver\DriverInterface,
    Behat\Mink\Element\ElementInterface,
    Behat\Mink\Exception\ElementNotFoundException;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Page element node.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class NodeElement extends TraversableElement
{
    private $xpath;

    /**
     * Initializes node element.
     *
     * @param string  $xpath   element xpath
     * @param Session $session session instance
     */
    public function __construct($xpath, Session $session)
    {
        $this->xpath = $xpath;

        parent::__construct($session);
    }

    /**
     * Returns XPath for handled element.
     *
     * @return string
     */
    public function getXpath()
    {
        return $this->xpath;
    }

    /**
     * Returns parent element to the current one.
     *
     * @return NodeElement
     */
    public function getParent()
    {
        return $this->find('xpath', '..');
    }

    /**
     * Returns current node tag name.
     *
     * @return string
     */
    public function getTagName()
    {
        return $this->getSession()->getDriver()->getTagName($this->getXpath());
    }

    /**
     * Returns element value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->getSession()->getDriver()->getValue($this->getXpath());
    }

    /**
     * Sets node value.
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->getSession()->getDriver()->setValue($this->getXpath(), $value);
    }

    /**
     * Checks whether element has attribute with specified name.
     *
     * @param string $name
     *
     * @return Boolean
     */
    public function hasAttribute($name)
    {
        return null !== $this->getSession()->getDriver()->getAttribute($this->getXpath(), $name);
    }

    /**
     * Returns specified attribute value.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function getAttribute($name)
    {
        return $this->getSession()->getDriver()->getAttribute($this->getXpath(), $name);
    }

    /**
     * Clicks current node.
     */
    public function click()
    {
        $this->getSession()->getDriver()->click($this->getXpath());
    }

    /**
     * Presses current button.
     */
    public function press()
    {
        $this->click();
    }

    /**
     * Double-clicks current node.
     */
    public function doubleClick()
    {
        $this->getSession()->getDriver()->doubleClick($this->getXpath());
    }

    /**
     * Right-clicks current node.
     */
    public function rightClick()
    {
        $this->getSession()->getDriver()->rightClick($this->getXpath());
    }

    /**
     * Checks current node if it's a checkbox field.
     */
    public function check()
    {
        $this->getSession()->getDriver()->check($this->getXpath());
    }

    /**
     * Unchecks current node if it's a checkbox field.
     */
    public function uncheck()
    {
        $this->getSession()->getDriver()->uncheck($this->getXpath());
    }

    /**
     * Checks whether current node is checked if it's a checkbox field.
     *
     * @return Boolean
     */
    public function isChecked()
    {
        return (Boolean) $this->getSession()->getDriver()->isChecked($this->getXpath());
    }

    /**
     * Selects current node specified option if it's a select field.
     *
     * @param string  $option
     * @param Boolean $multiple
     *
     * @throws ElementNotFoundException
     */
    public function selectOption($option, $multiple = false)
    {
        if ('select' !== $this->getTagName()) {
            $this->getSession()->getDriver()->selectOption($this->getXpath(), $option, $multiple);

            return;
        }

        $opt = $this->find('named', array(
            'option', $this->getSession()->getSelectorsHandler()->xpathLiteral($option)
        ));

        if (null === $opt) {
            throw new ElementNotFoundException(
                $this->getSession(), 'select option', 'value|text', $option
            );
        }

        $this->getSession()->getDriver()->selectOption(
            $this->getXpath(), $opt->getValue(), $multiple
        );
    }

    /**
     * Attach file to current node if it's a file input.
     *
     * @param string $path path to file (local)
     */
    public function attachFile($path)
    {
        $this->getSession()->getDriver()->attachFile($this->getXpath(), $path);
    }

    /**
     * Checks whether current node is visible on page.
     *
     * @return Boolean
     */
    public function isVisible()
    {
        return (Boolean) $this->getSession()->getDriver()->isVisible($this->getXpath());
    }

    /**
     * Simulates a mouse over on the element.
     */
    public function mouseOver()
    {
        $this->getSession()->getDriver()->mouseOver($this->getXpath());
    }

    /**
     * Drags current node onto other node.
     *
     * @param ElementInterface $destination other node
     */
    public function dragTo(ElementInterface $destination)
    {
        $this->getSession()->getDriver()->dragTo($this->getXpath(), $destination->getXpath());
    }

    /**
     * Brings focus to element.
     */
    public function focus()
    {
        $this->getSession()->getDriver()->focus($this->getXpath());
    }

    /**
     * Removes focus from element.
     */
    public function blur()
    {
        $this->getSession()->getDriver()->blur($this->getXpath());
    }

    /**
     * Presses specific keyboard key.
     *
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function keyPress($char, $modifier = null)
    {
        $this->getSession()->getDriver()->keyPress($this->getXpath(), $char, $modifier);
    }

    /**
     * Pressed down specific keyboard key.
     *
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function keyDown($char, $modifier = null)
    {
        $this->getSession()->getDriver()->keyDown($this->getXpath(), $char, $modifier);
    }

    /**
     * Pressed up specific keyboard key.
     *
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    public function keyUp($char, $modifier = null)
    {
        $this->getSession()->getDriver()->keyUp($this->getXpath(), $char, $modifier);
    }
}
