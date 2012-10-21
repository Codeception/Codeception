<?php

namespace Tests\Behat\Mink\Driver;

use Behat\Mink\Driver\GoutteDriver;

/**
 * @group gouttedriver
 */
class GoutteDriverTest extends HeadlessDriverTest
{
    protected static function getDriver()
    {
        return new GoutteDriver();
    }
}
