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
 * Mink base exception class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Exception extends \Exception
{
    private $session;

    /**
     * Initializes Mink exception.
     *
     * @param string     $message
     * @param Session    $session
     * @param integer    $code
     * @param \Exception $previous
     */
    public function __construct($message, Session $session = null, $code = 0, \Exception $previous = null)
    {
        $this->session = $session;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns exception session.
     *
     * @return Session
     */
    protected function getSession()
    {
        return $this->session;
    }

    /**
     * Prepends every line in a string with pipe (|).
     *
     * @param string $string
     *
     * @return string
     */
    protected function pipeString($string)
    {
        return '|  ' . strtr($string, array("\n" => "\n|  "));
    }

    /**
     * Removes response header/footer, letting only <body /> content and trim it.
     *
     * @param string  $string response content
     * @param integer $count  trim count
     *
     * @return string
     */
    protected function trimBody($string, $count = 1000)
    {
        $string = preg_replace(array('/^.*<body>/s', '/<\/body>.*$/s'), array('<body>', '</body>'), $string);
        $string = $this->trimString($string, $count);

        return $string;
    }

    /**
     * Trims string to specified number of chars.
     *
     * @param string  $string response content
     * @param integer $count  trim count
     *
     * @return string
     */
    protected function trimString($string, $count = 1000)
    {
        $string = trim($string);

        if ($count < mb_strlen($string)) {
            return mb_substr($string, 0, $count - 3) . '...';
        }

        return $string;
    }

    /**
     * Returns response information string.
     *
     * @return string
     */
    protected function getResponseInfo()
    {
        $driver = basename(str_replace('\\', '/', get_class($this->session->getDriver())));

        $info = '+--[ ';
        if (!in_array($driver, array('SahiDriver', 'SeleniumDriver'))) {
            $info .= 'HTTP/1.1 '.$this->session->getStatusCode().' | ';
        }
        $info .= $this->session->getCurrentUrl().' | '.$driver." ]\n|\n";

        return $info;
    }
}
