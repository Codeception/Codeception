<?php

class PhpTestCest
{
    public function phpFuntions(CodeGuy $I) {
        $I->execute(function() { return strtoupper('hello'); });
        $I->seeResultEquals('HELLO');
    }

    public function shouldWriteShoulds(CodeGuy $I) {
        $I->seeFeaturesEquals('write shoulds');
    }

    public function shouldUseCest(CodeGuy $I) {
        $I->haveFakeClass($scenario = \Codeception\Util\Stub::makeEmptyExcept('Codeception\Scenario', 'comment'));
        $I->executeMethod($scenario, 'comment', 'cool, that works!');
        $I->seeMethodInvoked($scenario,'addStep');

    }

}
