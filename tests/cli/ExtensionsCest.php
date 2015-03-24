<?php
use \CliGuy;

class ExtensionsCest
{
    // tests
    public function useAlternativeFormatter(CliGuy $I)
    {
        $I->wantTo('use alternative formatter delivered through extensions');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/FileExistsCept.php -c codeception_extended.yml');
        $I->dontSeeInShellOutput("Trying to check config");
        $I->seeInShellOutput('[+] check config');        
    }

    public function reRunFailedTests(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run unit FailingTest.php -c codeception_extended.yml --no-exit');
        $I->seeInShellOutput('FAILURES');
        $I->seeFileFound('failed','tests/_output');
        $I->seeFileContentsEqual(<<<EOF
tests/unit/FailingTest.php:testMe
EOF
);
        $I->executeCommand('run -g failed -c codeception_extended.yml --no-exit');
        $I->seeInShellOutput('Tests: 1, Assertions: 1, Failures: 1');
    }

    public function checkIfExtensionsReceiveCorrectOptions(CliGuy $I)
    {
        $I->wantTo('check if extensions receive correct options');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic -c codeception_extended.yml');
        $I->seeInShellOutput('Low verbosity');
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic -c codeception_extended.yml -v');
        $I->seeInShellOutput('Medium verbosity');
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic -c codeception_extended.yml -vv');
        $I->seeInShellOutput('High verbosity');
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic -c codeception_extended.yml -vvv');
        $I->seeInShellOutput('Extreme verbosity');
    }
}
