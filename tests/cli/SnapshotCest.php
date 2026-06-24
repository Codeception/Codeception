<?php

declare(strict_types=1);

use Tests\Support\CliTester;
use Codeception\Attribute\Before;
use Codeception\Attribute\Group;

final class SnapshotCest
{
    public function _openSnapshotSuite(CliTester $I)
    {
        $I->amInPath('tests/data/snapshots');
    }

    #[Before('_openSnapshotSuite')]
    public function runAllSnapshotTests(CliTester $I)
    {
        $I->executeCommand('run tests/SnapshotDataCest.php');
        $I->seeInShellOutput('OK (3 tests');
        $I->seeInShellOutput('Load snapshot and skip refresh');
        $I->seeInShellOutput('Load snapshot and refresh');
    }

    #[Group('user')]
    #[Before('_openSnapshotSuite')]
    public function runSnapshotRefresh(CliTester $I)
    {
        $I->executeCommand('run tests/SnapshotDataCest.php:loadSnapshotAndRefresh --debug --no-colors');
        $I->seeInShellOutput('Snapshot\UserSnapshot: assert');
        $I->seeInShellOutput('I grab column from database');
        $I->seeInShellOutput('Snapshot assertion failed');
        $I->seeInShellOutput('Snapshot data updated');
    }

    #[Before('_openSnapshotSuite')]
    public function runSnapshotRefreshFail(CliTester $I)
    {
        $I->executeCommand('run tests/SnapshotDataCest.php:loadSnapshotAndSkipRefresh --debug  --no-colors');
        $I->seeInShellOutput('Snapshot\UserSnapshot: assert');
        $I->seeInShellOutput('I grab column from database');
        $I->seeInShellOutput('Snapshot assertion failed');
        $I->dontSeeInShellOutput('Snapshot data updated');
    }

    #[Before('_openSnapshotSuite')]
    public function runSnapshotDiffDisplay(CliTester $I)
    {
        $I->executeCommand('run tests/SnapshotDisplayDiffCest.php');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('_openSnapshotSuite')]
    public function loadSnapshotInDebugAndFailOnProd(CliTester $I)
    {
        $I->executeCommand('run tests/SnapshotFailCest.php --debug');
        $I->seeInShellOutput('PASSED');
        $I->executeCommand('run tests/SnapshotFailCest.php --no-exit');
        $I->seeInShellOutput('FAILURES');
        $I->seeInShellOutput('Snapshot doesn\'t match real data');
    }

    public function generateGlobalSnapshot(\Tests\Support\Step\GeneratorSteps $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:snapshot Login');
        $I->seeFileWithGeneratedClass('Login', 'tests/_support/Snapshot');
        $I->dontSeeInThisFile('public function __construct(\DumbGuy $I)');
    }

    public function generateSuiteSnapshot(\Tests\Support\Step\GeneratorSteps $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:snapshot dummy Login');
        $I->seeFileWithGeneratedClass('Login', 'tests/_support/Snapshot/Dummy');
        $I->seeInThisFile('namespace Snapshot\\Dummy;');
        $I->seeInThisFile('class Login');
        $I->seeInThisFile('protected $dumbGuy;');
        $I->seeInThisFile('public function __construct(\DumbGuy $I)');
    }

    public function generateGlobalSnapshotInDifferentPath(\Tests\Support\Step\GeneratorSteps $I)
    {
        $I->executeCommand('generate:snapshot Login -c tests/data/sandbox');
        $I->amInPath('tests/data/sandbox');
        $I->seeFileWithGeneratedClass('Login', 'tests/_support/Snapshot');
        $I->dontSeeInThisFile('public function __construct(\DumbGuy $I)');
    }

    #[Before('_openSnapshotSuite')]
    public function runNonJsonContentSnapshotTests(CliTester $I)
    {
        $I->executeCommand('run tests/SnapshotNonJsonDataCest.php');
        $I->seeInShellOutput('OK (3 tests');
        $I->seeInShellOutput('Load snapshot and skip refresh');
        $I->seeInShellOutput('Load snapshot and refresh');
    }
}
