<?php
class BootstrapCest
{

    protected $bootstrapPath;

    function _before(\CliGuy $I)
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
        $I->seeInShellOutput('Building Actor classes for suites');
    }

    public function bootstrapWithNamespace(\CliGuy $I)
    {
        $I->executeCommand('bootstrap --namespace Generated');

        $I->seeInShellOutput('Building Actor classes for suites');
        $I->seeFileFound('codeception.yml');
        $I->seeInThisFile('namespace: Generated');
        $I->dontSeeInThisFile('namespace Generated\\');
        $this->checkFilesCreated($I);

        $I->seeFileFound('AcceptanceHelper.php');
        $I->seeInThisFile('namespace Generated\Codeception\Module;');

        $I->seeFileFound('AcceptanceTester.php');
        $I->seeInThisFile('namespace Generated;');
    }

    public function bootstrapWithActor(\CliGuy $I)
    {
        $I->executeCommand('bootstrap --actor Ninja');
        $I->seeFileFound('AcceptanceNinja.php','tests/acceptance');
    }

    public function bootstrapCompatibilityProject(\CliGuy $I) {
        $I->executeCommand('bootstrap --compat');
        $I->seeFileFound('codeception.yml');
        $this->checkCompatFilesCreated($I);
        $I->seeInShellOutput('Building Actor classes for suites');
    }

    public function bootstrapCompatibilityWithNamespace(\CliGuy $I)
    {
        $I->executeCommand('bootstrap --namespace Generated --compat');

        $I->seeInShellOutput('Building Actor classes for suites');
        $I->seeFileFound('codeception.yml');
        $I->seeInThisFile('namespace: Generated');
        $I->dontSeeInThisFile('namespace Generated\\');
        $this->checkCompatFilesCreated($I);

        $I->seeFileFound('WebHelper.php');
        $I->seeInThisFile('namespace Generated\Codeception\Module;');

        $I->seeFileFound('WebGuy.php');
        $I->seeInThisFile('namespace Generated;');
    }

    protected function checkFilesCreated(\CliGuy $I)
    {
        $I->seeDirFound('tests/_support');
        $I->seeDirFound('tests/_data');
        $I->seeDirFound('tests/_output');


        $I->seeFileFound('functional.suite.yml','tests');
        $I->seeFileFound('acceptance.suite.yml','tests');
        $I->seeFileFound('unit.suite.yml','tests');

        $I->seeFileFound('_bootstrap.php','tests/acceptance');
        $I->seeFileFound('_bootstrap.php','tests/functional');
        $I->seeFileFound('_bootstrap.php','tests/unit');

        $I->seeFileFound('AcceptanceTester.php','tests/acceptance');
        $I->seeFileFound('FunctionalTester.php','tests/functional');
        $I->seeFileFound('UnitTester.php','tests/unit');

        $I->seeFileFound('AcceptanceHelper.php','tests/_support');
        $I->seeFileFound('FunctionalHelper.php','tests/_support');
        $I->seeFileFound('UnitHelper.php','tests/_support');
    }


    protected function checkCompatFilesCreated(\CliGuy $I)
    {
        $I->seeDirFound('tests/_log');
        $I->seeDirFound('tests/_data');
        $I->seeDirFound('tests/_helpers');

        $I->seeFileFound('functional.suite.yml','tests');
        $I->seeFileFound('acceptance.suite.yml','tests');
        $I->seeFileFound('unit.suite.yml','tests');

        $I->seeFileFound('_bootstrap.php','tests/acceptance');
        $I->seeFileFound('_bootstrap.php','tests/functional');
        $I->seeFileFound('_bootstrap.php','tests/unit');

        $I->seeFileFound('WebGuy.php','tests/acceptance');
        $I->seeFileFound('TestGuy.php','tests/functional');
        $I->seeFileFound('CodeGuy.php','tests/unit');

        $I->seeFileFound('WebHelper.php','tests/_helpers');
        $I->seeFileFound('TestHelper.php','tests/_helpers');
        $I->seeFileFound('CodeHelper.php','tests/_helpers');
    }        

}
