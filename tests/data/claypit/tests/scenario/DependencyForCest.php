<?php

namespace Codeception\Demo\Depends;

class DependencyForCest
{
    /**
     * @group dataprovider
     */
    public function forTestPurpose(): int
    {
        return 1;
    }
}
