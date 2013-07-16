<?php
class IncludedCest {

    public function _before()
    {
        \Codeception\Util\FileSystem::doEmptyDir('tests/data/included/_log');
        file_put_contents('tests/data/included/_log/.gitkeep','');
    }

    public function runSuitesFromIncludedConfigs(\CliGuy $I)
    {
        $I->amInPath('tests/data/included');
        $I->executeCommand('run');
        $I->seeInShellOutput('[Jazz]');
        $I->seeInShellOutput('Suite Jazz.functional started');
        $I->seeInShellOutput('[Jazz\Pianist]');
        $I->seeInShellOutput('Suite Jazz\Pianist.functional started');
        $I->seeInShellOutput('[Shire]');
        $I->seeInShellOutput('Suite Shire.functional started');
    }

    public function runIncludedWithXmlOutput(\CliGuy $I)
    {
        $I->amInPath('tests/data/included');
        $I->executeCommand('run --xml');
        $I->amInPath('_log');
        $I->seeFileFound('report.xml');
        $I->seeInThisFile('<testsuite name="Jazz.functional" tests="1" assertions="1"');
        $I->seeInThisFile('<testsuite name="Jazz\Pianist.functional" tests="1" assertions="1"');
        $I->seeInThisFile('<testsuite name="Shire.functional" tests="1" assertions="1"');
        $I->seeInThisFile('<testcase file="HobbitCept.php"');
        $I->seeInThisFile('<testcase file="DemoCept.php"');
        $I->seeInThisFile('<testcase file="PianistCept.php"');
    }

    public function runIncludedWithHtmlOutput(\CliGuy $I)
    {
        $I->amInPath('tests/data/included');
        $I->executeCommand('run --html');
        $I->amInPath('_log');
        $I->seeFileFound('report.html');
        $I->seeInThisFile('Codeception Results');
        $I->seeInThisFile('Jazz.functional Tests');
        $I->seeInThisFile('check that jazz musicians can add numbers');
        $I->seeInThisFile('Jazz\Pianist.functional Tests');
        $I->seeInThisFile('check that jazz pianists can add numbers');
        $I->seeInThisFile('Shire.functional Tests');
    }

    public function runIncludedWithCoverage(\CliGuy $I)
    {
        $I->amInPath('tests/data/included');
        $I->executeCommand('run --xml --coverage');
        $I->amInPath('_log');
        $I->seeFileFound('coverage.xml');
        $I->seeInThisFile('<class name="BillEvans" namespace="Jazz\Pianist">');
        $I->seeInThisFile('<class name="Musician" namespace="Jazz">');
        $I->seeInThisFile('<class name="Hobbit" namespace="Shire">');
    }
}


