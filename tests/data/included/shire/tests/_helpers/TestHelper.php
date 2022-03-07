<?php

namespace Shire;

use PHPUnit\Framework\Assert;

class TestHelper extends \Codeception\Module
{
    public function seeEquals($expected, $actual): void
    {
        Assert::assertEquals($expected, $actual);
    }
}
