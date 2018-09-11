<?php

class SnapshotCest
{

    public function _openSnapshotSuite(CliGuy $I)
    {
        $I->amInPath('tests/data/snapshots');
    }

    /**
     * @before _openSnapshotSuite
     * @param CliGuy $I
     */
    public function runAllSnapshotTests(CliGuy $I)
    {
        $I->executeCommand('run tests/SnapshotDataCest.php');
        $I->seeInShellOutput('OK (3 tests');
        $I->seeInShellOutput('Load snapshot and skip refresh');
        $I->seeInShellOutput('Load snapshot and refresh');
    }

    /**
     * @before _openSnapshotSuite
     * @param CliGuy $I
     */
    public function runSnapshotRefresh(CliGuy $I)
    {
        $I->executeCommand('run tests/SnapshotDataCest.php:loadSnapshotAndRefresh --debug --no-colors');
        $I->seeInShellOutput('Snapshot\UserSnapshot: assert');
        $I->seeInShellOutput('I grab column from database');
        $I->seeInShellOutput('Snapshot assertion failed');
        $I->seeInShellOutput('Snapshot data updated');
    }

    /**
     * @before _openSnapshotSuite
     * @param CliGuy $I
     */
    public function runSnapshotRefreshFail(CliGuy $I)
    {
        $I->executeCommand('run tests/SnapshotDataCest.php:loadSnapshotAndSkipRefresh --debug  --no-colors');
        $I->seeInShellOutput('Snapshot\UserSnapshot: assert');
        $I->seeInShellOutput('I grab column from database');
        $I->seeInShellOutput('Snapshot assertion failed');
        $I->dontSeeInShellOutput('Snapshot data updated');
    }

    /**
     * @before _openSnapshotSuite
     * @param CliGuy $I
     */
    public function loadSnapshotInDebugAndFailOnProd(CliGuy $I)
    {
        $I->executeCommand('run tests/SnapshotFailCest.php --debug');
        $I->seeInShellOutput('PASSED');
        $I->executeCommand('run tests/SnapshotFailCest.php --no-exit');
        $I->seeInShellOutput('FAILURES');
        $I->seeInShellOutput('Snapshot doesn\'t match real data');
    }

    public function generateGlobalSnapshot(CliGuy\GeneratorSteps $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:snapshot Login');
        $I->seeFileWithGeneratedClass('Login', 'tests/_support/Snapshot');
        $I->dontSeeInThisFile('public function __construct(\DumbGuy $I)');
        $I->seeFileFound('tests/_bootstrap.php');
    }

    public function generateSuiteSnapshot(CliGuy\GeneratorSteps $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('generate:snapshot dummy Login');
        $I->seeFileWithGeneratedClass('Login', 'tests/_support/Snapshot/Dummy');
        $I->seeInThisFile('namespace Snapshot\\Dummy;');
        $I->seeInThisFile('class Login');
        $I->seeInThisFile('protected $dumbGuy;');
        $I->seeInThisFile('public function __construct(\DumbGuy $I)');
    }

    public function generateGlobalSnapshotInDifferentPath(CliGuy\GeneratorSteps $I)
    {
        $I->executeCommand('generate:snapshot Login -c tests/data/sandbox');
        $I->amInPath('tests/data/sandbox');
        $I->seeFileWithGeneratedClass('Login', 'tests/_support/Snapshot');
        $I->dontSeeInThisFile('public function __construct(\DumbGuy $I)');
        $I->seeFileFound('tests/_bootstrap.php');
    }

}