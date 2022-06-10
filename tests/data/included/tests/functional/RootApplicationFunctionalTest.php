<?php

declare(strict_types=1);

namespace data\included\tests\functional;

use Codeception\Test\Unit;

/**
 * This is not a Cept test case because it does not matter for what we are testing.
 */
class RootApplicationFunctionalTest extends Unit
{
    
    public function testBarEqualsBar()
    {
        $this->assertSame('bar', 'bar');
    }
}