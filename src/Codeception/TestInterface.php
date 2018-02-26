<?php

namespace Codeception;

use Codeception\Test\Metadata;

interface TestInterface extends \PHPUnit\Framework\Test
{
    /**
     * @return Metadata
     */
    public function getMetadata();
}
