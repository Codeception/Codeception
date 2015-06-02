<?php
class IncludedCest {

    public function _before()
    {
        \Codeception\Util\FileSystem::doEmptyDir('tests/data/included/_log');
        file_put_contents('tests/data/included/_log/.gitkeep','');
    }

    /**
     * @param CliGuy $I
     */
    protected function moveToIncluded(\CliGuy $I)
    {
        $I->amInPath('tests/data/included');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runSuitesFromIncludedConfigs(\CliGuy $I)
    {
        $I->executeCommand('run');
        $I->seeInShellOutput('[Jazz]');
        $I->seeInShellOutput('Jazz.functional Tests');
        $I->seeInShellOutput('[Jazz\Pianist]');
        $I->seeInShellOutput('Jazz\Pianist.functional Tests');
        $I->seeInShellOutput('[Shire]');
        $I->seeInShellOutput('Shire.functional Tests');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runIncludedWithXmlOutput(\CliGuy $I)
    {
        $I->executeCommand('run --xml');
        $I->amInPath('_log');
        $I->seeFileFound('report.xml');
        $I->seeInThisFile('<testsuite name="Jazz.functional" tests="1" assertions="1"');
        $I->seeInThisFile('<testsuite name="Jazz\Pianist.functional" tests="1" assertions="1"');
        $I->seeInThisFile('<testsuite name="Shire.functional" tests="1" assertions="1"');
        $I->seeInThisFile('<testcase name="Hobbit"');
        $I->seeInThisFile('<testcase name="Demo"');
        $I->seeInThisFile('<testcase name="Pianist"');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runIncludedWithHtmlOutput(\CliGuy $I)
    {
        $I->executeCommand('run --html');
        $I->amInPath('_log');
        $I->seeFileFound('report.html');
        $I->seeInThisFile('Codeception Results');
        $I->seeInThisFile('Jazz.functional Tests');
        $I->seeInThisFile('Check that jazz musicians can add numbers');
        $I->seeInThisFile('Jazz\Pianist.functional Tests');
        $I->seeInThisFile('Check that jazz pianists can add numbers');
        $I->seeInThisFile('Shire.functional Tests');
    }

    /**
     * @before moveToIncluded
     * @group coverage
     * @param CliGuy $I
     */
    public function runIncludedWithCoverage(\CliGuy $I)
    {
        $I->executeCommand('run --coverage-xml');
        $I->amInPath('_log');
        $I->seeFileFound('coverage.xml');
        $I->seeInThisFile('<class name="BillEvans" namespace="Jazz\Pianist">');
        $I->seeInThisFile('<class name="Musician" namespace="Jazz">');
        $I->seeInThisFile('<class name="Hobbit" namespace="Shire">');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function buildIncluded(\CliGuy $I)
    {
        $I->executeCommand('build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInShellOutput('Jazz\\TestGuy');
        $I->seeInShellOutput('Jazz\\Pianist\\TestGuy');
        $I->seeInShellOutput('Shire\\TestGuy');

    }
}


