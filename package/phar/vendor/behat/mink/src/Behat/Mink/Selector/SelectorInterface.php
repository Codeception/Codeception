<?php

namespace Behat\Mink\Selector;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Mink selector engine interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SelectorInterface
{
    /**
     * Translates provided locator into XPath.
     *
     * @param string $locator current selector locator
     *
     * @return string
     */
    function translateToXPath($locator);
}
