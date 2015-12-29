<?php

namespace Codeception;

use Codeception\Test\Metadata;

interface Testable extends \PHPUnit_Framework_Test
{
    /**
     * @return Metadata
     */
    public function getMetadata();
}
