<?php
use \Codeception\Util\Stub as Stub;

class ScenarioCest
{
    public $class = '\Codeception\Scenario';

    public function run(CodeGuy $I) {

        $I->wantTo('run steps from scenario');
        $I->haveFakeClass($test = Stub::makeEmpty('\Codeception\TestCase\Cept'));
        $I->haveFakeClass($scenario = Stub::make('\Codeception\Scenario', array('test' => $test, 'steps' => Stub::factory('\Codeception\Step', 2))));

        $I->executeTestedMethodOn($scenario)
            ->seeMethodInvoked($test,'runStep')
            ->seePropertyEquals($scenario, 'currentStep', 1);
    }
}
