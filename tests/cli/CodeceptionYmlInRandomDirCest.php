<?php
class CodeceptionYmlInRandomDirCest
{
    /**
     * @param CliGuy $I
     */
    public function runTestPath(\CliGuy $I)
    {
        $I->amInPath('tests/data/codeception_yml_in_random_dir');
        $I->executeCommand('run -c random/subdir/codeception.yml tests/unit/ExampleCest.php');

        $I->seeResultCodeIs(0);
        $I->dontSeeInShellOutput('RuntimeException');
        $I->dontSeeInShellOutput('could not be found');
    }
}
