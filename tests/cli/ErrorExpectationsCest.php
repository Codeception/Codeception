<?php

use PHPUnit\Runner\Version;

class ErrorExpectationsCest
{
    public function _before(\CliGuy $I, \Codeception\Scenario $scenario)
    {
        if (version_compare(Version::id(), '9.6', '>=')) {
            $scenario->skip('Error expectations are not supported on PHPUnit 10 and deprecated in 9.6');
        }
        $I->amInPath('tests/data/error_handling');
    }

    public function expectNoticeWorks(\CliGuy $I)
    {
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testNotice');
        $I->seeInShellOutput("OK (");
    }

    public function expectWarningWorks(\CliGuy $I)
    {
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testWarning');
        $I->seeInShellOutput('OK (');
    }

    public function expectErrorWorks(\CliGuy $I)
    {
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testError');
        $I->seeInShellOutput('OK (');
    }

    public function expectDeprecationWorks(\CliGuy $I)
    {
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testDeprecation');
        $I->seeInShellOutput('OK (');
    }
}
