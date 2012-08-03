<?php

namespace Behat\Mink\Exception;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Mink driver exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DriverException extends Exception
{
    /**
     * Initializes exception.
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, null, $code, $previous);
    }
}
