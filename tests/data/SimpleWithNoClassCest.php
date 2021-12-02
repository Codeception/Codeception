<?php

class SimpleWithNoClassCest
{

    public function phpFuncitons(CodeGuy $I) {
        $I->execute(fn() => strtoupper('hello'));
        $I->seeResultEquals('HELLO');
    }

}
