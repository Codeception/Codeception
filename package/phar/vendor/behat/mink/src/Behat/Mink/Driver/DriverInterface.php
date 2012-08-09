<?php

namespace Behat\Mink\Driver;

use Behat\Mink\Session;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Driver interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface DriverInterface
{
    /**
     * Sets driver's current session.
     *
     * @param Session $session
     */
    function setSession(Session $session);

    /**
     * Starts driver.
     */
    function start();

    /**
     * Checks whether driver is started.
     *
     * @return Boolean
     */
    function isStarted();

    /**
     * Stops driver.
     */
    function stop();

    /**
     * Resets driver.
     */
    function reset();

    /**
     * Visit specified URL.
     *
     * @param string $url url of the page
     */
    function visit($url);

    /**
     * Returns current URL address.
     *
     * @return string
     */
    function getCurrentUrl();

    /**
     * Reloads current page.
     */
    function reload();

    /**
     * Moves browser forward 1 page.
     */
    function forward();

    /**
     * Moves browser backward 1 page.
     */
    function back();

    /**
     * Sets HTTP Basic authentication parameters
     *
     * @param string|Boolean $user     user name or false to disable authentication
     * @param string         $password password
     */
    function setBasicAuth($user, $password);

    /**
     * Switches to specific browser window.
     *
     * @param string $name window name (null for switching back to main window)
     */
    function switchToWindow($name = null);

    /**
     * Switches to specific iFrame.
     *
     * @param string $name iframe name (null for switching back)
     */
    function switchToIFrame($name = null);

    /**
     * Sets specific request header on client.
     *
     * @param string $name
     * @param string $value
     */
    function setRequestHeader($name, $value);

    /**
     * Returns last response headers.
     *
     * @return array
     */
    function getResponseHeaders();

    /**
     * Sets cookie.
     *
     * @param string $name
     * @param string $value
     */
    function setCookie($name, $value = null);

    /**
     * Returns cookie by name.
     *
     * @param string $name
     *
     * @return string|null
     */
    function getCookie($name);

    /**
     * Returns last response status code.
     *
     * @return integer
     */
    function getStatusCode();

    /**
     * Returns last response content.
     *
     * @return string
     */
    function getContent();

    /**
     * Finds elements with specified XPath query.
     *
     * @param string $xpath
     *
     * @return array array of NodeElements
     */
    function find($xpath);

    /**
     * Returns element's tag name by it's XPath query.
     *
     * @param string $xpath
     *
     * @return string
     */
    function getTagName($xpath);

    /**
     * Returns element's text by it's XPath query.
     *
     * @param string $xpath
     *
     * @return string
     */
    function getText($xpath);

    /**
     * Returns element's html by it's XPath query.
     *
     * @param string $xpath
     *
     * @return string
     */
    function getHtml($xpath);

    /**
     * Returns element's attribute by it's XPath query.
     *
     * @param string $xpath
     * @param string $name
     *
     * @return mixed
     */
    function getAttribute($xpath, $name);

    /**
     * Returns element's value by it's XPath query.
     *
     * @param string $xpath
     *
     * @return mixed
     */
    function getValue($xpath);

    /**
     * Sets element's value by it's XPath query.
     *
     * @param string $xpath
     * @param string $value
     */
    function setValue($xpath, $value);

    /**
     * Checks checkbox by it's XPath query.
     *
     * @param string $xpath
     */
    function check($xpath);

    /**
     * Unchecks checkbox by it's XPath query.
     *
     * @param string $xpath
     */
    function uncheck($xpath);

    /**
     * Checks whether checkbox checked located by it's XPath query.
     *
     * @param string $xpath
     *
     * @return Boolean
     */
    function isChecked($xpath);

    /**
     * Selects option from select field located by it's XPath query.
     *
     * @param string  $xpath
     * @param string  $value
     * @param Boolean $multiple
     */
    function selectOption($xpath, $value, $multiple = false);

    /**
     * Clicks button or link located by it's XPath query.
     *
     * @param string $xpath
     */
    function click($xpath);

    /**
     * Double-clicks button or link located by it's XPath query.
     *
     * @param string $xpath
     */
    function doubleClick($xpath);

    /**
     * Right-clicks button or link located by it's XPath query.
     *
     * @param string $xpath
     */
    function rightClick($xpath);

    /**
     * Attaches file path to file field located by it's XPath query.
     *
     * @param string $xpath
     * @param string $path
     */
    function attachFile($xpath, $path);

    /**
     * Checks whether element visible located by it's XPath query.
     *
     * @param string $xpath
     *
     * @return Boolean
     */
    function isVisible($xpath);

    /**
     * Simulates a mouse over on the element.
     *
     * @param string $xpath
     */
    function mouseOver($xpath);

    /**
     * Brings focus to element.
     *
     * @param string $xpath
     */
    function focus($xpath);

    /**
     * Removes focus from element.
     *
     * @param string $xpath
     */
    function blur($xpath);

    /**
     * Presses specific keyboard key.
     *
     * @param string $xpath
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    function keyPress($xpath, $char, $modifier = null);

    /**
     * Pressed down specific keyboard key.
     *
     * @param string $xpath
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    function keyDown($xpath, $char, $modifier = null);

    /**
     * Pressed up specific keyboard key.
     *
     * @param string $xpath
     * @param mixed  $char     could be either char ('b') or char-code (98)
     * @param string $modifier keyboard modifier (could be 'ctrl', 'alt', 'shift' or 'meta')
     */
    function keyUp($xpath, $char, $modifier = null);

    /**
     * Drag one element onto another.
     *
     * @param string $sourceXpath
     * @param string $destinationXpath
     */
    function dragTo($sourceXpath, $destinationXpath);

    /**
     * Executes JS script.
     *
     * @param string $script
     */
    function executeScript($script);

    /**
     * Evaluates JS script.
     *
     * @param string $script
     *
     * @return mixed
     */
    function evaluateScript($script);

    /**
     * Waits some time or until JS condition turns true.
     *
     * @param integer $time      time in milliseconds
     * @param string  $condition JS condition
     */
    function wait($time, $condition);
}
