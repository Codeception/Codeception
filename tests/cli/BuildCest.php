<?php

declare(strict_types=1);
// @group core

use Codeception\Scenario;

final class BuildCest
{
    /** @var string */
    private $originalCliHelperContents;

    public function _before()
    {
        $this->originalCliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
    }

    public function _after()
    {
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $this->originalCliHelperContents);
    }

    public function buildsActionsForAClass(CliGuy $I)
    {
        $I->wantToTest('build command');
        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CodeGuy.php');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->seeInThisFile('seeFileFound(');
        $I->seeInThisFile('public function assertSame($expected, $actual, $message = "") {');
    }

    public function usesTypehintsWherePossible(CliGuy $I, Scenario $scenario)
    {
        $I->wantToTest('generate typehints with generated actions');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function grabFromOutput($regex)', 'public function grabFromOutput($regex): int', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->openFile(codecept_root_dir('tests/support/CliHelper.php'));
        $I->seeInThisFile('public function grabFromOutput($regex): int');
        $I->seeInThisFile('return $match[1]');
    }

    public function noReturnForVoidType(CliGuy $I, Scenario $scenario)
    {
        $I->wantToTest('no return keyword generated for void typehint');

        $cliHelperContents = file_get_contents(codecept_root_dir('tests/support/CliHelper.php'));
        $cliHelperContents = str_replace('public function seeDirFound($dir)', 'public function seeDirFound($dir): void', $cliHelperContents);
        file_put_contents(codecept_root_dir('tests/support/CliHelper.php'), $cliHelperContents);

        $I->runShellCommand('php codecept build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInSupportDir('CliGuy.php');
        $I->seeInThisFile('class CliGuy extends \Codeception\Actor');
        $I->seeInThisFile('use _generated\CliGuyActions');
        $I->seeFileFound('CliGuyActions.php', 'tests/support/_generated');
        $I->openFile(codecept_root_dir('tests/support/CliHelper.php'));
        $I->seeInThisFile('public function seeDirFound($dir): void');
        $I->dontSeeInThisFile('return $this->assertTrue(is_dir(');
        $I->seeInThisFile('$this->assertTrue(is_dir(');
    }
}
