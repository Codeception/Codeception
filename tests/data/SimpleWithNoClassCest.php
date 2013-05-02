<?php

class SimpleWithNoClassCest
{

    public function phpFuncitons(CodeGuy $I) {
        $I->execute(function() { return strtoupper('hello'); });
        $I->seeResultEquals('HELLO');
    }

}
