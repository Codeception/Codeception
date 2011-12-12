<?php
class ScenarioCest
{
    public $class = '\Codeception\Scenario';

    public function run(CodeGuy $I) {
        $I->wantTo('run steps from scenario');
        $I->haveFakeClass($test = Stub::makeEmpty('\Codeception\TestCase\Cept'));
        $I->haveFakeClass($scenario = Stub::make('\Codeception\Scenario', array(
            'test' => $test,
            'steps' => array(
                Stub::makeEmpty('\Codeception\Step\Action'),
                Stub::makeEmpty('\Codeception\Step\Comment')
            )
        )));
        $I->executeTestedMethodOn($scenario);
        // $I->seeMethodInvoked($test,'runStep');
        $I->seePropertyEquals($scenario, 'currentStep', 1);
    }
}
