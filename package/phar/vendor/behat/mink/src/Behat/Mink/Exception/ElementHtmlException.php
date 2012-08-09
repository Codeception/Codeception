<?php

namespace Behat\Mink\Exception;

use Behat\Mink\Session,
    Behat\Mink\Element\Element;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Mink's element HTML exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ElementHtmlException extends ExpectationException
{
    /**
     * Element instance.
     *
     * @var Element
     */
    protected $element;

    /**
     * Initializes exception.
     *
     * @param string     $message   optional message
     * @param Session    $session   session instance
     * @param Element    $element   element
     * @param \Exception $exception expectation exception
     */
    public function __construct($message = null, Session $session, Element $element, \Exception $exception = null)
    {
        $this->element = $element;

        parent::__construct($message, $session, $exception);
    }

    /**
     * Returns exception message with additional context info.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $pageText = $this->trimString($this->element->getHtml());
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
