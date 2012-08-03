<?php

namespace Behat\Mink\Exception;

use Behat\Mink\Session;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Mink "element not found" exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ElementNotFoundException extends Exception
{
    /**
     * Initializes exception.
     *
     * @param Session $session  session instance
     * @param string  $type     element type
     * @param string  $selector element selector type
     * @param string  $locator  element locator
     */
    public function __construct(Session $session, $type = null, $selector = null, $locator = null)
    {
        $message = '';

        if (null !== $type) {
            $message .= ucfirst($type);
        } else {
            $message .= 'Tag';
        }

        if (null !== $locator) {
            if (null === $selector || in_array($selector, array('css', 'xpath'))) {
                $selector = 'matching '.($selector ?: 'locator');
            } else {
                $selector = 'with '.$selector;
            }
            $message .= ' '.$selector.' "' . $locator . '" ';
        }

        $message .= 'not found.';

        parent::__construct($message, $session);
    }

    /**
     * Returns exception message with additional context info.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $pageText = $this->trimBody($this->getSession()->getPage()->getContent());
            $string   = sprintf("%s\n\n%s%s",
                $this->getMessage(),
                $this->getResponseInfo(),
                $this->pipeString($pageText."\n")
            );
        } catch (\Exception $e) {
            return $this->getMessage();
        }

        return $string;
    }
}
