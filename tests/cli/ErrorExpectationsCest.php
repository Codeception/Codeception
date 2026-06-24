<?php

declare(strict_types=1);

use Tests\Support\CliTester;
use PHPUnit\Runner\Version;

final class ErrorExpectationsCest
{
    public function _before(CliTester $I, \Codeception\Scenario $scenario)
    {
        if (version_compare(Version::id(), '9.6', '>=')) {
            $scenario->skip('Error expectations are not supported on PHPUnit 10 and deprecated in 9.6');
        }
        $I->amInPath('tests/data/error_handling');
    }

    public function expectNoticeWorks(CliTester $I)
    {
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testNotice');
        $I->seeInShellOutput("OK (");
    }

    public function expectWarningWorks(CliTester $I)
    {
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testWarning');
        $I->seeInShellOutput('OK (');
    }

    public function expectErrorWorks(CliTester $I)
    {
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testError');
        $I->seeInShellOutput('OK (');
    }

    public function expectDeprecationWorks(CliTester $I)
    {
        $I->executeCommand('run tests/unit/ErrorExceptionTest.php:testDeprecation');
        $I->seeInShellOutput('OK (');
    }
}
