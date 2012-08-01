<?php

namespace Behat\Mink\Selector;

use Symfony\Component\CssSelector\CssSelector as CSS;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CSS selector engine. Transforms CSS to XPath.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CssSelector implements SelectorInterface
{
    /**
     * Translates CSS into XPath.
     *
     * @param string $locator current selector locator
     *
     * @return string
     */
    public function translateToXPath($locator)
    {
        return CSS::toXPath($locator);
    }
}
