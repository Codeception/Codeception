<?php

class PhpTestCest
{

    public function phpFuncitons(CodeGuy $I) {
        $I->execute(function() { return strtoupper('hello'); });
        $I->seeResultEquals('HELLO');
    }

    public function shouldTryWritingShoulds(CodeGuy $I) {
        $I->seeFeaturesEquals('try writing shoulds');
    }

}
