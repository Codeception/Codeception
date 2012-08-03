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
 * Base element.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Element implements ElementInterface
{
    private $session;

    /**
     * Initialize element.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Returns element session.
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Checks whether element with specified selector exists.
     *
     * @param string $selector selector engine name
     * @param string $locator  selector locator
     *
     * @return Boolean
     */
    public function has($selector, $locator)
    {
        return null !== $this->find($selector, $locator);
    }

    /**
     * Finds first element with specified selector.
     *
     * @param string $selector selector engine name
     * @param string $locator  selector locator
     *
     * @return NodeElement|null
     */
    public function find($selector, $locator)
    {
        $items = $this->findAll($selector, $locator);

        return count($items) ? current($items) : null;
    }

    /**
     * Finds all elements with specified selector.
     *
     * @param string $selector selector engine name
     * @param string $locator  selector locator
     *
     * @return array
     */
    public function findAll($selector, $locator)
    {
        $xpath = $this->getSession()->getSelectorsHandler()->selectorToXpath($selector, $locator);

        // add parent xpath before element selector
        if (0 === strpos($xpath, '/')) {
            $xpath = $this->getXpath().$xpath;
        } else {
            $xpath = $this->getXpath().'/'.$xpath;
        }

        return $this->getSession()->getDriver()->find($xpath);
    }

    /**
     * Returns element text (inside tag).
     *
     * @return string|null
     */
    public function getText()
    {
        return $this->getSession()->getDriver()->getText($this->getXpath());
    }

    /**
     * Returns element html.
     *
     * @return string|null
     */
    public function getHtml()
    {
        return $this->getSession()->getDriver()->getHtml($this->getXpath());
    }
}
