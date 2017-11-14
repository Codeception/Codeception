<?php
class CodeceptionYmlInTestsDirCest
{
    /**
     * @param CliGuy $I
     */
    public function runTestPath(\CliGuy $I)
    {
        $I->amInPath('tests/data/codeception_yml_in_tests_dir');
        $I->executeCommand('run unit/ExampleCest.php');
        
        $I->seeResultCodeIs(0);
        $I->dontSeeInShellOutput('RuntimeException');
        $I->dontSeeInShellOutput('could not be found');
    }
}
