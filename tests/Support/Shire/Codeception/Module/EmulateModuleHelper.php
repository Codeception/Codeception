<?php

namespace Shire\Codeception\Module;

use PHPUnit\Framework\Assert;

class EmulateModuleHelper extends \Codeception\Module
{
    /**
     * @var mixed
     */
    public $scenario;
    public int $assertions = 0;

    public function seeEquals($expected, $actual)
    {
        Assert::assertEquals($expected, $actual);
        ++$this->assertions;
    }

    public function seeFeaturesEquals($expected)
    {
        Assert::assertEquals($expected, $this->scenario->getFeature());
    }

    public function _before(\Codeception\TestInterface $test)
    {
        $this->scenario = $test->getScenario();
    }
}
