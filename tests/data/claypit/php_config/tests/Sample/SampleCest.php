<?php

declare(strict_types=1);

namespace PhpConfig;

use PhpConfig\Support\SampleTester;

final class SampleCest
{
    public function loadsThroughPhpConfig(SampleTester $I): void
    {
        $I->assertTrue(true);
    }
}
