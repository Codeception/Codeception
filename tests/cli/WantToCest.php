<?php

declare(strict_types=1);

use Tests\Support\CliTester;

final class WantToCest
{
    public function iWantToSetsFeatureInCeptFormat(CliTester $I)
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCept.php');
        $I->seeInShellOutput('+ WantToCept: Check if wantTo works');
    }

    public function iWantToSetsFeatureInCestFormat(CliTester $I)
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCest.php:^IWantTo');
        $I->seeInShellOutput('+ WantToCest: Check if I->wantTo works');
    }

    /**
     * Tests https://github.com/Codeception/Codeception/issues/4123
     */
    public function testerWantDoesntSetFeatureInCestFormat(CliTester $I)
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCest.php:^TesterWantTo');
        $I->seeInShellOutput('+ WantToCest: Tester want to');
    }

    public function iWantToWithVariableIsIgnored(CliTester $I)
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCest.php:Variable');
        $I->seeInShellOutput('+ WantToCest: Variable argument of want to');
    }

    /**
     * Tests https://github.com/Codeception/Codeception/issues/4124
     */
    public function iWantToDoesntOverrideDataproviderData(CliTester $I)
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCest.php:DataProviderIWantTo');
        $I->seeInShellOutput('+ WantToCest: Check if I->wantTo doesn\\\'t override data provider data | "aaa"');
        $I->seeInShellOutput('+ WantToCest: Check if I->wantTo doesn\\\'t override data provider data | "bbb"');
    }

    public function testerWantToDoesntOverrideDataproviderData(CliTester $I)
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCest.php:DataProviderTesterWantTo');
        $I->seeInShellOutput('+ WantToCest: Data provider tester want to | "aaa"');
        $I->seeInShellOutput('+ WantToCest: Data provider tester want to | "bbb"');
    }

    public function wantToTextIsUsedInXmlReport(CliTester $I)
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run unit --xml');
        $I->seeShellOutputMatches('!\- JUNIT XML report generated in file:.*report\.xml!');
        $I->seeFileFound('tests/_output/report.xml');
        $I->seeInThisFile('WantToCept.php" feature="check if wantTo works"');
        $I->seeInThisFile('WantToCest.php" feature="check if I-&gt;wantTo works"');
        $I->seeInThisFile('WantToCest.php" feature="tester want to');
        $I->seeInThisFile('WantToCest.php" feature="check if I-&gt;wantTo doesn\\\'t override data provider data | &quot;aaa&quot;"');
        $I->seeInThisFile('WantToCest.php" feature="check if I-&gt;wantTo doesn\\\'t override data provider data | &quot;bbb&quot;"');
        $I->seeInThisFile('WantToCest.php" feature="data provider tester want to | &quot;aaa&quot;"');
        $I->seeInThisFile('WantToCest.php" feature="data provider tester want to | &quot;bbb&quot;"');
        $I->seeInThisFile('WantToCest.php" feature="variable argument of want to"');
    }
}
