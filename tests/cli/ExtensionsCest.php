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
        $I->executeCommand('run unit -c codeception_extended.yml --no-exit');
        $I->seeInShellOutput('FAILURES');
        $I->seeFileFound('failed','tests/_log');
        $I->seeFileContentsEqual(<<<EOF
tests/unit/DependsTest.php:testOne
tests/unit/FailingTest.php:testMe
EOF
);
        $I->executeCommand('run -g failed -c codeception_extended.yml --no-exit');
        $I->seeInShellOutput('Tests: 2, Assertions: 2, Failures: 2');
    }
}