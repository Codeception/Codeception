<?php

class ErrorExpectationsCest
{
    public function _before(\CliGuy $I, \Codeception\Scenario $scenario)
    {
        if (\PHPUnit\Runner\Version::series() > 9) {
            $scenario->skip('Error expectations are not supported on PHPUnit 10');
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
