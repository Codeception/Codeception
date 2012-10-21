<?php

namespace Behat\Mink\Element;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Document element.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DocumentElement extends TraversableElement
{
    /**
     * Returns XPath for handled element.
     *
     * @return string
     */
    public function getXpath()
    {
        return '//html';
    }

    /**
     * Returns document content.
     *
     * @return string
     */
    public function getContent()
    {
        return trim($this->getSession()->getDriver()->getContent());
    }

    /**
     * Check whether document has specified content.
     *
     * @param string $content
     *
     * @return Boolean
     */
    public function hasContent($content)
    {
        return $this->has('named', array(
            'content', $this->getSession()->getSelectorsHandler()->xpathLiteral($content)
        ));
    }
}
