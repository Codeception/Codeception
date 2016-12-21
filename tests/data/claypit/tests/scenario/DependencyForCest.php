<?php
namespace Codeception\Demo\Depends;

class DependencyForCest
{
    /**
     * @group dataprovider
     */
    public function forTestPurpose()
    {
        return 1;
    }
}
