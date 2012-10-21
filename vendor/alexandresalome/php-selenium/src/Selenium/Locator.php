<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Selenium;

/**
 * Helping class for locating elements in Selenium.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Locator
{
    /**
     * Creates locator with @id or @name attribute.
     *
     * @param string $idOrName Attribute value
     *
     * @return string The locator string
     */
    static public function IdOrName($idOrName)
    {
        return 'identifier='.$idOrName;
    }

    /**
     * Creates locator with @id attribute.
     *
     * @param string $id Attribute value
     *
     * @return string The locator string
     */
    static public function id($id)
    {
        return 'id='.$id;
    }

    /**
     * Creates locator with @name attribute.
     *
     * @param string $id           Attribute value
     * @param string $valuePattern Pattern for the field to locate
     * @param int    $index        Index of the element
     *
     * @return string The locator string
     */
    static public function name($name, $valuePattern = null, $index = null)
    {
        $result = 'name='.$name;

        if (null !== $valuePattern) {
            $result .= ' value='.$valuePattern;
        }

        if (null !== $index) {
            $result .= ' index='.$index;
        }

        return $result;
    }

    /**
     * Creates locator with the Javascript DOM API (document.forms[1] for example).
     *
     * @param string $expression The DOM expression
     *
     * @return string The locator string
     */
    static public function javascriptDom($expression)
    {
        return 'dom='.$expression;
    }

    /**
     * Creates locator with XPath selector
     *
     * @param string $xpath The XPath
     *
     * @return string The locator string
     */
    static public function xpath($xpath)
    {
        return 'xpath='.$xpath;
    }


    /**
     * Creates locator for a link using a pattern.
     *
     * @param string $pattern The text pattern
     *
     * @return string The locator string
     */
    static public function linkContaining($pattern)
    {
        return 'link='.$pattern;
    }


    /**
     * Creates locator with CSS selector
     *
     * @param string $css The CSS selector
     *
     * @return string The locator string
     */
    static public function css($css)
    {
        return 'css='.$css;
    }
}
