<?php

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Codeception\Test\Unit;

final class Depends2Test extends Unit
{
    #[Group('depends')]
    #[Depends(DependsTest::class . ':testTwo')]
    public function testThree()
    {
        $this->assertTrue(true);
    }
}
