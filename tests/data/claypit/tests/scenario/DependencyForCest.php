<?php

namespace Codeception\Demo\Depends;

use Codeception\Attribute\Group;

class DependencyForCest
{
    #[Group('dataprovider')]
    public function forTestPurpose(): int
    {
        return 1;
    }
}
