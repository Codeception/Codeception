<?php

namespace Shire\Codeception\Module;

class EmulateModuleHelper extends \Codeception\Module
{
    /**
     * @var mixed
     */
    public $scenario;
    public int $assertions = 0;

    public function seeEquals($expected, $actual)
    {
        \PHPUnit_Framework_Assert::assertEquals($expected, $actual);
        ++$this->assertions;
    }

    public function seeFeaturesEquals($expected)
    {
        \PHPUnit_Framework_Assert::assertEquals($expected, $this->scenario->getFeature());
    }

    public function _before(\Codeception\TestInterface $test)
    {
        $this->scenario = $test->getScenario();
    }
}
