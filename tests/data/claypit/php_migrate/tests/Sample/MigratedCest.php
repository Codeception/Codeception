<?php

declare(strict_types=1);

namespace Migrated;

use Migrated\Support\SampleTester;

final class MigratedCest
{
    public function passesThroughMigratedConfig(SampleTester $I): void
    {
        $I->assertTrue(true);
    }
}
