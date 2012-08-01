<?php
use Codeception\Util\Stub;

class WebDebugCest {
    public $class = '\Codeception\Module\WebDebug';

    public function makeAScreenshot(CodeGuy $I) {
        $I->wantTo('save screenshot');
        $I->haveStub($selenium = Stub::makeEmpty('\Codeception\Module\Selenium'));
        $I->haveFakeClass($stub = Stub::make($this->class, array(
            'test' => Stub::makeEmpty('\Codeception\TestCase\Cept', array('getFileName' => function () { return 'testtest'; })),
            'module' => $selenium ))
        );
        $I->executeTestedMethodOn($stub);
        $I->seeMethodInvoked($selenium, '_saveScreenshot');
        $I->seeMethodNotInvoked($stub, 'debug');
    }

    public function generateFilename(CodeGuy $I) {
        $I->haveFakeClass($stub = Stub::make($this->class, array(
                'test' => Stub::makeEmpty('\Codeception\TestCase\Cept', array('getFileName' => function () { return 'testtest'; })),
        )));
        $I->executeTestedMethod($stub);
        $I->seeResultEquals(\Codeception\Configuration::logDir().'debug'.DIRECTORY_SEPARATOR.'testtest - 1');
        $I->executeTestedMethod($stub, 'mytest');
        $I->seeResultEquals(\Codeception\Configuration::logDir().'debug'.DIRECTORY_SEPARATOR.'testtest - 2 - mytest');


    }
}