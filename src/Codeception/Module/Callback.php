<?php
namespace Codeception\Module;

/**
 * Module for rapid test development where many difficult configurations and server environments
 *
 * ## Status
 *
 * * Maintainer: **korotovsky**
 * * Stability: **beta**
 * * Contact: dmitry.korotovsky@gmail.com
 *
 */

class Callback extends \Codeception\Module
{
    /**
     * Executes given callback
     */
    public function callback(\Closure $callback)
    {
        return $callback();
    }
}
