<?php

namespace Behat\Mink;

use Behat\Mink\Element\NodeElement,
    Behat\Mink\Exception\ElementNotFoundException,
    Behat\Mink\Exception\ExpectationException,
    Behat\Mink\Exception\ResponseTextException,
    Behat\Mink\Exception\ElementHtmlException,
    Behat\Mink\Exception\ElementTextException;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Mink web assertions tool.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class WebAssert
{
    protected $session;

    /**
     * Initializes assertion engine.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Checks that current session address is equals to provided one.
     *
     * @param string $page
     *
     * @throws ExpectationException
     */
    public function addressEquals($page)
    {
        $expected = $this->cleanScriptnameFromPath(parse_url($page, PHP_URL_PATH));
        $actual   = $this->getCurrentUrlPath();

        if ($actual !== $expected) {
            $message = sprintf('Current page is "%s", but "%s" expected.', $actual, $expected);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that current session address is not equals to provided one.
     *
     * @param string $page
     *
     * @throws ExpectationException
     */
    public function addressNotEquals($page)
    {
        $expected = $this->cleanScriptnameFromPath(parse_url($page, PHP_URL_PATH));
        $actual   = $this->getCurrentUrlPath();

        if ($actual === $expected) {
            $message = sprintf('Current page is "%s", but should not be.', $actual);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that current session address matches regex.
     *
     * @param string $regex
     *
     * @throws ExpectationException
     */
    public function addressMatches($regex)
    {
        $actual = $this->getCurrentUrlPath();

        if (!preg_match($regex, $actual)) {
            $message = sprintf('Current page "%s" does not match the regex "%s".', $actual, $regex);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that specified cookie exists
     *
     * @param string $name  cookie name
     *
     * @throws Behat\Mink\Exception\ExpectationException
     */
    public function cookieExists($name)
    {
        if ($this->session->getCookie($name) === null) {
            $message = sprintf('Cookie "%s" is not set, but should be.', $name);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that current response code equals to provided one.
     *
     * @param integer $code
     *
     * @throws ExpectationException
     */
    public function statusCodeEquals($code)
    {
        $actual = $this->session->getStatusCode();

        if (intval($code) !== intval($actual)) {
            $message = sprintf('Current response status code is %d, but %d expected.', $actual, $code);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that current response code not equals to provided one.
     *
     * @param integer $code
     *
     * @throws ExpectationException
     */
    public function statusCodeNotEquals($code)
    {
        $actual = $this->session->getStatusCode();

        if (intval($code) === intval($actual)) {
            $message = sprintf('Current response status code is %d, but should not be.', $actual);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that current page contains text.
     *
     * @param string $text
     *
     * @throws ResponseTextException
     */
    public function pageTextContains($text)
    {
        $actual = $this->session->getPage()->getText();
        $regex  = '/'.preg_quote($text, '/').'/ui';

        if (!preg_match($regex, $actual)) {
            $message = sprintf('The text "%s" was not found anywhere in the text of the current page.', $text);
            throw new ResponseTextException($message, $this->session);
        }
    }

    /**
     * Checks that current page does not contains text.
     *
     * @param string $text
     *
     * @throws ResponseTextException
     */
    public function pageTextNotContains($text)
    {
        $actual = $this->session->getPage()->getText();
        $regex  = '/'.preg_quote($text, '/').'/ui';

        if (preg_match($regex, $actual)) {
            $message = sprintf('The text "%s" appears in the text of this page, but it should not.', $text);
            throw new ResponseTextException($message, $this->session);
        }
    }

    /**
     * Checks that current page text matches regex.
     *
     * @param string $regex
     *
     * @throws ResponseTextException
     */
    public function pageTextMatches($regex)
    {
        $actual = $this->session->getPage()->getText();

        if (!preg_match($regex, $actual)) {
            $message = sprintf('The pattern %s was not found anywhere in the text of the current page.', $regex);
            throw new ResponseTextException($message, $this->session);
        }
    }

    /**
     * Checks that current page text does not matches regex.
     *
     * @param string $regex
     *
     * @throws ResponseTextException
     */
    public function pageTextNotMatches($regex)
    {
        $actual = $this->session->getPage()->getText();

        if (preg_match($regex, $actual)) {
            $message = sprintf('The pattern %s was found in the text of the current page, but it should not.', $regex);
            throw new ResponseTextException($message, $this->session);
        }
    }

    /**
     * Checks that page HTML (response content) contains text.
     *
     * @param string $text
     *
     * @throws ExpectationException
     */
    public function responseContains($text)
    {
        $actual = $this->session->getPage()->getContent();
        $regex  = '/'.preg_quote($text, '/').'/ui';

        if (!preg_match($regex, $actual)) {
            $message = sprintf('The string "%s" was not found anywhere in the HTML response of the current page.', $text);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that page HTML (response content) does not contains text.
     *
     * @param string $text
     *
     * @throws ExpectationException
     */
    public function responseNotContains($text)
    {
        $actual = $this->session->getPage()->getContent();
        $regex  = '/'.preg_quote($text, '/').'/ui';

        if (preg_match($regex, $actual)) {
            $message = sprintf('The string "%s" appears in the HTML response of this page, but it should not.', $text);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that page HTML (response content) matches regex.
     *
     * @param string $regex
     *
     * @throws ExpectationException
     */
    public function responseMatches($regex)
    {
        $actual = $this->session->getPage()->getContent();

        if (!preg_match($regex, $actual)) {
            $message = sprintf('The pattern %s was not found anywhere in the HTML response of the page.', $regex);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that page HTML (response content) does not matches regex.
     *
     * @param $regex
     *
     * @throws ExpectationException
     */
    public function responseNotMatches($regex)
    {
        $actual = $this->session->getPage()->getContent();

        if (preg_match($regex, $actual)) {
            $message = sprintf('The pattern %s was found in the HTML response of the page, but it should not.', $regex);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that there is specified number of specific elements on the page.
     *
     * @param string  $selectorType element selector type (css, xpath)
     * @param string  $selector     element selector
     * @param integer $count        expected count
     *
     * @throws ExpectationException
     */
    public function elementsCount($selectorType, $selector, $count)
    {
        $nodes = $this->session->getPage()->findAll($selectorType, $selector);

        if (intval($count) !== count($nodes)) {
            $message = sprintf('%d elements matching %s "%s" found on the page, but should be %d.', count($nodes), $selectorType, $selector, $count);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that specific element exists on the current page.
     *
     * @param string $selectorType element selector type (css, xpath)
     * @param string $selector     element selector
     *
     * @return NodeElement
     *
     * @throws ElementNotFoundException
     */
    public function elementExists($selectorType, $selector)
    {
        $node = $this->session->getPage()->find($selectorType, $selector);

        if (null === $node) {
            throw new ElementNotFoundException($this->session, 'element', $selectorType, $selector);
        }

        return $node;
    }

    /**
     * Checks that specific element does not exists on the current page.
     *
     * @param string $selectorType element selector type (css, xpath)
     * @param string $selector     element selector
     *
     * @throws ExpectationException
     */
    public function elementNotExists($selectorType, $selector)
    {
        $node = $this->session->getPage()->find($selectorType, $selector);

        if (null !== $node) {
            $message = sprintf('An element matching %s "%s" appears on this page, but it should not.', $selectorType, $selector);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that specific element contains text.
     *
     * @param string $selectorType element selector type (css, xpath)
     * @param string $selector     element selector
     * @param string $text         expected text
     *
     * @throws ElementTextException
     */
    public function elementTextContains($selectorType, $selector, $text)
    {
        $element = $this->elementExists($selectorType, $selector);
        $actual  = $element->getText();
        $regex   = '/'.preg_quote($text, '/').'/ui';

        if (!preg_match($regex, $actual)) {
            $message = sprintf('The text "%s" was not found in the text of the element matching %s "%s".', $text, $selectorType, $selector);
            throw new ElementTextException($message, $this->session, $element);
        }
    }

    /**
     * Checks that specific element does not contains text.
     *
     * @param string $selectorType element selector type (css, xpath)
     * @param string $selector     element selector
     * @param string $text         expected text
     *
     * @throws ElementTextException
     */
    public function elementTextNotContains($selectorType, $selector, $text)
    {
        $element = $this->elementExists($selectorType, $selector);
        $actual  = $element->getText();
        $regex   = '/'.preg_quote($text, '/').'/ui';

        if (preg_match($regex, $actual)) {
            $message = sprintf('The text "%s" appears in the text of the element matching %s "%s", but it should not.', $text, $selectorType, $selector);
            throw new ElementTextException($message, $this->session, $element);
        }
    }

    /**
     * Checks that specific element contains HTML.
     *
     * @param string $selectorType element selector type (css, xpath)
     * @param string $selector     element selector
     * @param string $html         expected text
     *
     * @throws ElementHtmlException
     */
    public function elementContains($selectorType, $selector, $html)
    {
        $element = $this->elementExists($selectorType, $selector);
        $actual  = $element->getHtml();
        $regex   = '/'.preg_quote($html, '/').'/ui';

        if (!preg_match($regex, $actual)) {
            $message = sprintf('The string "%s" was not found in the HTML of the element matching %s "%s".', $html, $selectorType, $selector);
            throw new ElementHtmlException($message, $this->session, $element);
        }
    }

    /**
     * Checks that specific element does not contains HTML.
     *
     * @param string $selectorType element selector type (css, xpath)
     * @param string $selector     element selector
     * @param string $html         expected text
     *
     * @throws ElementHtmlException
     */
    public function elementNotContains($selectorType, $selector, $html)
    {
        $element = $this->elementExists($selectorType, $selector);
        $actual  = $element->getHtml();
        $regex   = '/'.preg_quote($html, '/').'/ui';

        if (preg_match($regex, $actual)) {
            $message = sprintf('The string "%s" appears in the HTML of the element matching %s "%s", but it should not.', $html, $selectorType, $selector);
            throw new ElementHtmlException($message, $this->session, $element);
        }
    }

    /**
     * Checks that specific field exists on the current page.
     *
     * @param string $field field id|name|label|value
     *
     * @return NodeElement
     *
     * @throws ElementNotFoundException
     */
    public function fieldExists($field)
    {
        $node = $this->session->getPage()->findField($field);

        if (null === $node) {
            throw new ElementNotFoundException($this->session, 'form field', 'id|name|label|value', $field);
        }

        return $node;
    }

    /**
     * Checks that specific field does not exists on the current page.
     *
     * @param string $field field id|name|label|value
     *
     * @throws ExpectationException
     */
    public function fieldNotExists($field)
    {
        $node = $this->session->getPage()->findField($field);

        if (null !== $node) {
            $message = sprintf('A field "%s" appears on this page, but it should not.', $field);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that specific field have provided value.
     *
     * @param string $field field id|name|label|value
     * @param string $value field value
     *
     * @throws ExpectationException
     */
    public function fieldValueEquals($field, $value)
    {
        $node   = $this->fieldExists($field);
        $actual = $node->getValue();
        $regex  = '/^'.preg_quote($value, '$/').'/ui';

        if (!preg_match($regex, $actual)) {
            $message = sprintf('The field "%s" value is "%s", but "%s" expected.', $field, $actual, $value);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that specific field have provided value.
     *
     * @param string $field field id|name|label|value
     * @param string $value field value
     *
     * @throws ExpectationException
     */
    public function fieldValueNotEquals($field, $value)
    {
        $node   = $this->fieldExists($field);
        $actual = $node->getValue();
        $regex  = '/^'.preg_quote($value, '$/').'/ui';

        if (preg_match($regex, $actual)) {
            $message = sprintf('The field "%s" value is "%s", but it should not be.', $field, $actual);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that specific checkbox is checked.
     *
     * @param string $field field id|name|label|value
     *
     * @throws ExpectationException
     */
    public function checkboxChecked($field)
    {
        $node = $this->fieldExists($field);

        if (!$node->isChecked()) {
            $message = sprintf('Checkbox "%s" is not checked, but it should be.', $field);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Checks that specific checkbox is unchecked.
     *
     * @param string $field field id|name|label|value
     *
     * @throws ExpectationException
     */
    public function checkboxNotChecked($field)
    {
        $node = $this->fieldExists($field);

        if ($node->isChecked()) {
            $message = sprintf('Checkbox "%s" is checked, but it should not be.', $field);
            throw new ExpectationException($message, $this->session);
        }
    }

    /**
     * Gets current url of the page.
     *
     * @return string
     */
    protected function getCurrentUrlPath()
    {
        return $this->cleanScriptnameFromPath(
            parse_url($this->session->getCurrentUrl(), PHP_URL_PATH)
        );
    }

    /**
     * Trims scriptname from the URL.
     *
     * @param string $path
     *
     * @return string
     */
    protected function cleanScriptnameFromPath($path)
    {
        return preg_replace('/^\/[^\.\/]+\.php/', '', $path);
    }
}
