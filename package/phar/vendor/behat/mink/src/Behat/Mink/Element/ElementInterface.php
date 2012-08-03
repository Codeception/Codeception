<?php

namespace Behat\Mink\Element;

use Behat\Mink\Session;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Element interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ElementInterface
{
    /**
     * Returns XPath for handled element.
     *
     * @return string
     */
    function getXpath();

    /**
     * Returns element's session.
     *
     * @return Session
     */
    function getSession();

    /**
     * Checks whether element with specified selector exists.
     *
     * @param string $selector selector engine name
     * @param string $locator  selector locator
     *
     * @return Boolean
     */
    function has($selector, $locator);

    /**
     * Finds first element with specified selector.
     *
     * @param string $selector selector engine name
     * @param string $locator  selector locator
     *
     * @return NodeElement|null
     */
    function find($selector, $locator);

    /**
     * Finds all elements with specified selector.
     *
     * @param string $selector selector engine name
     * @param string $locator  selector locator
     *
     * @return array
     */
    function findAll($selector, $locator);

    /**
     * Returns element text (inside tag).
     *
     * @return string|null
     */
    function getText();

    /**
     * Returns element html.
     *
     * @return string|null
     */
    function getHtml();
}
