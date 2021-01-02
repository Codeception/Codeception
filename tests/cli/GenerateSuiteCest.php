<?php

declare(strict_types=1);

final class GenerateSuiteCest
{
    public function generateSimpleSuite(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:suite house HouseGuy');
        $I->seeFileFound('House.suite.yml', 'tests');
        $I->expect('actor class is generated');
        $I->seeInThisFile('actor: HouseGuy');
        $I->seeDirFound('tests/house');

        $I->expect('suite is not created due to dashes');
        $I->executeCommand('generate:suite invalid-dash-suite', false);
        $I->seeResultCodeIs(1);
        $I->seeInShellOutput('contains invalid characters');
    }

    public function generateSuiteWithCustomConfig(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('bootstrap --empty src/FooBar --namespace FooBar');
        $I->executeCommand('generate:suite house HouseGuy -c src/FooBar');
        $I->seeDirFound('src/FooBar/tests/house');
        $I->seeFileFound('House.suite.yml', 'src/FooBar/tests');
        $I->expect('guy class is generated');
        $I->seeInThisFile('actor: HouseGuy');

        $I->expect('suite is not created due to dashes');
        $I->executeCommand('generate:suite invalid-dash-suite', false);
        $I->seeResultCodeIs(1);
        $I->seeInShellOutput('contains invalid characters');
    }
}
