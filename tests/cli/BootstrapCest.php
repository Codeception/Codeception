<?php


class BootstrapCest
{

    public function bootstrapProject(\CliGuy $I) {
        $I->amInPath('tests/data/sandbox/tests/_data/');
        $I->executeCommand('bootstrap');
        $I->seeFileFound('codeception.yml');
        $this->checkFilesCreated($I);
        $I->seeInShellOutput('Building Guy classes for suites');
    }

    public function bootstrapWithNamespace(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox/tests/_data/');
        $I->executeCommand('bootstrap --namespace Generated');

        $I->seeInShellOutput('Building Guy classes for suites');
        $I->seeFileFound('codeception.yml');
        $I->seeInThisFile('namespace: Generated');
        $I->dontSeeInThisFile('namespace Generated\\');
        $this->checkFilesCreated($I);

        $I->seeFileFound('WebHelper.php');
        $I->seeInThisFile('namespace Generated\Codeception\Module;');

        $I->seeFileFound('WebGuy.php');
        $I->seeInThisFile('namespace Generated;');
    }
    
    protected function checkFilesCreated(\CliGuy $I)
    {
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
