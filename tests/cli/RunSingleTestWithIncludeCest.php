<?php

class RunSingleTestWithIncludeCest
{
    public function run(\CliGuy $I)
    {
        $I->amInPath('tests/data/single_test_with_include');
        $I->wantTo('execute one test with include in config');

        $I->executeCommand('run unit/ExampleCest.php');

        $I->seeResultCodeIs(0);
        $I->dontSeeInShellOutput('RuntimeException');
        $I->dontSeeInShellOutput('could not be found');
    }
}
