<?php

declare(strict_types=1);

/**
 * @guy CliGuy\GeneratorSteps
 */
final class GeneratePageObjectCest
{
    public function generateGlobalPageObject(CliGuy\GeneratorSteps $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:page Login');
        $I->seeFileWithGeneratedClass('Login', 'tests/_support/Page');
        $I->dontSeeInThisFile('public function __construct(\DumbGuy $I)');
    }

    public function generateSuitePageObject(CliGuy\GeneratorSteps $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:page dummy Login');
        $I->seeFileWithGeneratedClass('Login', 'tests/_support/Page/Dummy');
        $I->seeInThisFile('namespace Page\\Dummy;');
        $I->seeInThisFile('class Login');
        $I->seeInThisFile('protected $dumbGuy;');
        $I->seeInThisFile('public function __construct(\DumbGuy $I)');
    }

    public function generateGlobalPageObjectInDifferentPath(CliGuy\GeneratorSteps $I)
    {
        $I->executeCommand('generate:page Login -c tests/data/sandbox');
        $I->amInPath('tests/data/sandbox');
        $I->seeFileWithGeneratedClass('Login', 'tests/_support/Page');
        $I->dontSeeInThisFile('public function __construct(\DumbGuy $I)');
    }
}
