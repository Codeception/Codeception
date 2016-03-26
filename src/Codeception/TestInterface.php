<?php

namespace Codeception;

use Codeception\Test\Metadata;

interface TestInterface extends \PHPUnit_Framework_Test, TestCase // BC Compat
{
    /**
     * @return Metadata
     */
    public function getMetadata();
}
