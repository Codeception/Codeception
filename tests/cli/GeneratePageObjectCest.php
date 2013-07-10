<?php

class GeneratePageObjectCest
{
    public function generateGlobalPageObject(CliGuy $I) {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:page Login');
        $I->seeFileFound('LoginPage.php', 'tests/_pages');
        $I->seeInThisFile('class LoginPage');
        $I->seeInThisFile('const URL = ');
        $I->dontSeeInThisFile('public static function of(\DummyGuy $I)');
    }

    public function generateSuitePageObject(CliGuy $I) {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:page dummy Login');
        $I->seeFileFound('LoginPage.php', 'tests/dummy/_pages');
        $I->seeInThisFile('class LoginPage');
        $I->seeInThisFile('protected $dumbGuy;');
        $I->seeInThisFile('public static function of(\DumbGuy $I)');

    }

}