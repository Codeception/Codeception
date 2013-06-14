<?php
namespace Codeception\Module;

/**
 * Module for rapid test development with lots of complex configurations and server environments
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
    public function runTimeCallback(\Closure $callback)
    {
        return $callback();
    }
}
