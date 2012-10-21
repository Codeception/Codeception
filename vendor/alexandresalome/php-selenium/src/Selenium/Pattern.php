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
 * Pattern helper for text matching in Selenium
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Pattern
{
    /**
     * Generates a pattern from a Glob selector.
     *
     * @param string $pattern The Glob pattern
     *
     * @return string The selenium pattern
     */
    static public function glob($pattern)
    {
        return 'glob:'.$pattern;
    }

    /**
     * Generates a pattern from a regexp.
     *
     * @param string $regext The regexp
     *
     * @return string The selenium pattern
     */
    static public function regexp($regexp)
    {
        return 'regexp:'.$regexp;
    }

    /**
     * Generates a pattern from an exact value.
     *
     * @param string $string The value
     *
     * @return string The selenium pattern
     */
    static public function exact($string)
    {
        return 'exact:'.$string;
    }
}
