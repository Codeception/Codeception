<?php
class BootstrapCest
{

    protected $bootstrapPath;

    public function _before(\CliGuy $I)
    {
        $this->bootstrapPath = 'tests/data/sandbox/boot'.uniqid();
        @mkdir($this->bootstrapPath, 0777, true);
        $I->amInPath($this->bootstrapPath);
    }

    public function bootstrap(\CliGuy $I)
    {
        $I->executeCommand('bootstrap');
        $I->seeFileFound('codeception.yml');
        $this->checkFilesCreated($I);
    }

    public function bootstrapWithNamespace(\CliGuy $I)
    {
        $I->executeCommand('bootstrap --namespace Generated');

        $I->seeFileFound('codeception.yml');
        $I->seeInThisFile('namespace: Generated');
        $I->dontSeeInThisFile('namespace Generated\\');
        $this->checkFilesCreated($I);

        $I->seeFileFound('Acceptance.php', 'tests/_support/Helper');
        $I->seeInThisFile('namespace Generated\Helper;');

        $I->seeFileFound('AcceptanceTester.php', 'tests/_support');
        $I->seeInThisFile('namespace Generated;');
    }

    public function bootstrapWithNamespaceShortcut(\CliGuy $I)
    {
        $I->executeCommand('bootstrap -s Generated');

        $I->seeFileFound('codeception.yml');
        $I->seeInThisFile('namespace: Generated');
        $I->dontSeeInThisFile('namespace Generated\\');
        $this->checkFilesCreated($I);

        $I->seeFileFound('Acceptance.php', 'tests/_support/Helper');
        $I->seeInThisFile('namespace Generated\Helper;');

        $I->seeFileFound('AcceptanceTester.php', 'tests/_support');
        $I->seeInThisFile('namespace Generated;');
    }

    public function bootstrapWithActor(\CliGuy $I)
    {
        $I->executeCommand('bootstrap --actor Ninja');
        $I->seeFileFound('AcceptanceNinja.php', 'tests/_support/');
    }


    public function bootstrapEmpty(\CliGuy $I)
    {
        $I->executeCommand('bootstrap --empty');
        $I->dontSeeFileFound('tests/acceptance');
        $I->seeFileFound('codeception.yml');
    }

    public function bootstrapFromInit(\CliGuy $I)
    {
        $I->executeCommand('init bootstrap');
        $this->checkFilesCreated($I);
    }

    public function bootstrapFromInitUsingClassName(\CliGuy $I)
    {
        $I->executeCommand('init "Codeception\Template\Bootstrap"');
        $this->checkFilesCreated($I);
    }

    protected function checkFilesCreated(\CliGuy $I)
    {
        $I->seeDirFound('tests/_support');
        $I->seeDirFound('tests/_data');
        $I->seeDirFound('tests/_output');

        $I->seeFileFound('functional.suite.yml', 'tests');
        $I->seeFileFound('acceptance.suite.yml', 'tests');
        $I->seeFileFound('unit.suite.yml', 'tests');

        $I->seeFileFound('AcceptanceTester.php', 'tests/_support');
        $I->seeFileFound('FunctionalTester.php', 'tests/_support');
        $I->seeFileFound('UnitTester.php', 'tests/_support');

        $I->seeFileFound('Acceptance.php', 'tests/_support/Helper');
        $I->seeFileFound('Functional.php', 'tests/_support/Helper');
        $I->seeFileFound('Unit.php', 'tests/_support/Helper');
    }

}
