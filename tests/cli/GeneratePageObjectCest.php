<?php

/**
 * @guy CliGuy\GeneratorSteps
 */
class GeneratePageObjectCest
{
    public function generateGlobalPageObject(CliGuy\GeneratorSteps $I) {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:page Login');
        $I->seeFileWithGeneratedClass('LoginPage','tests/_pages');
        $I->seeInThisFile('const URL = ');
        $I->dontSeeInThisFile('public static function of(DummyGuy $I)');
        $I->seeFileFound('tests/_bootstrap.php');
        $I->seeInThisFile("\\Codeception\\Util\\Autoload::registerSuffix('Page', __DIR__.DIRECTORY_SEPARATOR.'_pages'");
    }

    public function generateSuitePageObject(CliGuy\GeneratorSteps $I) {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:page dummy Login');
        $I->seeFileWithGeneratedClass('LoginPage','tests/dummy/_pages');
        $I->seeInThisFile('class LoginPage');
        $I->seeInThisFile('protected $dumbGuy;');
        $I->seeInThisFile('public static function of(DumbGuy $I)');
        $I->seeAutoloaderWasAdded('Page', 'tests/dummy');

    }


}