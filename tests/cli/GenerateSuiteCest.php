<?php
class GenerateSuiteCest
{
    public function generateSimpleSuite(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:suite house HouseGuy');
        $I->seeFileFound('house.suite.yml', 'tests');
        $I->expect('actor class is generated');
        $I->seeInThisFile('actor: HouseGuy');
        $I->seeInThisFile('- \Helper\House');
        $I->seeFileFound('House.php', 'tests/_support/Helper');
        $I->seeInThisFile('namespace Helper;');
        $I->seeDirFound('tests/house');
        $I->seeFileFound('_bootstrap.php', 'tests/house');

        $I->expect('suite is not created due to dashes');
        $I->executeCommand('generate:suite invalid-dash-suite');
        $I->seeInShellOutput('contains invalid characters');
    }

    public function generateSuiteWithCustomConfig(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('bootstrap --empty src/FooBar --namespace FooBar');
        $I->executeCommand('generate:suite house HouseGuy -c src/FooBar');
        $I->seeDirFound('src/FooBar/tests/house');
        $I->seeFileFound('house.suite.yml', 'src/FooBar/tests');
        $I->expect('guy class is generated');
        $I->seeInThisFile('actor: HouseGuy');
        $I->seeInThisFile('- \FooBar\Helper\House');
        $I->seeFileFound('House.php', 'src/FooBar/tests/_support/Helper');
        $I->seeInThisFile('namespace FooBar\Helper;');

        $I->expect('suite is not created due to dashes');
        $I->executeCommand('generate:suite invalid-dash-suite');
        $I->seeInShellOutput('contains invalid characters');
    }
}