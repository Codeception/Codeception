<?php

class WantToCest
{
    public function iWantToSetsFeatureInCeptFormat(CliGuy $I): void
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCept.php');
        $I->seeInShellOutput('+ WantToCept: Check if wantTo works');
    }

    public function iWantToSetsFeatureInCestFormat(CliGuy $I): void
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCest.php:^IWantTo');
        $I->seeInShellOutput('+ WantToCest: Check if I->wantTo works');
    }

    public function testerWantToSetsFeatureInCestFormat(CliGuy $I): void
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCest.php:^TesterWantTo');
        $I->seeInShellOutput('+ WantToCest: Check if tester->wantTo works');
    }

    public function variablePassedToIWantToIsEvaluated(CliGuy $I): void
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCest.php:Variable');
        $I->seeInShellOutput('+ WantToCest: Check if variable wantTo is evaluated');
    }

    public function iWantToIncorrectlyOverridesDataproviderData(CliGuy $I): void
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCest.php:DataProviderIWantTo');
        $I->seeInShellOutput('+ WantToCest: Check if I->wantTo doesn\'t override data provider data');
    }

    public function testerWantToIncorrectlyOverridesDataproviderData(CliGuy $I): void
    {
        $I->amInPath('tests/data/want_to');
        $I->executeCommand('run --no-ansi unit WantToCest.php:DataProviderTesterWantTo');
        $I->seeInShellOutput('+ WantToCest: Check if tester->wantTo doesn\'t override data provider data');
    }

    public function wantToTextIsUsedInXmlReport(CliGuy $I): void
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
