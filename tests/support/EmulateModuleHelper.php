<?php

namespace Codeception\Module;

use PHPUnit\Framework\Assert;

class EmulateModuleHelper extends \Codeception\Module
{
    public string $feature;
    public int $assertions = 0;

    public function seeEquals($expected, $actual)
    {
        Assert::assertEquals($expected, $actual);
        ++$this->assertions;
    }

    public function seeFeaturesEquals($expected)
    {
        Assert::assertEquals($expected, $this->feature);
    }

    public function _before(\Codeception\TestInterface $test)
    {
        $this->feature = $test->getMetadata()->getFeature();
    }
}
