<?php

declare(strict_types=1);

use Tests\Support\CliTester;

#[\Codeception\Attribute\Group('bootstrap')]
final class BootstrapCest
{
    public function _before(CliTester $I)
    {
        $bootstrapPath = 'tests/data/sandbox/boot' . uniqid();
        @mkdir($bootstrapPath, 0777, true);
        $I->amInPath($bootstrapPath);
    }

    public function bootstrap(CliTester $I)
    {
        $I->executeCommand('bootstrap');
        $I->seeFileFound('codeception.yml');
        $I->seeFileFound('tests/Support/_generated/.gitignore');
        $I->seeInThisFile("*\n!.gitignore\n");
        $I->seeFileFound('tests/_output/.gitignore');
        $I->seeInThisFile("*\n!.gitignore\n");
        $I->dontSeeFileFound('tests/_output/.gitkeep');
        $this->checkFilesCreated($I);
    }

    public function bootstrapWithNamespace(CliTester $I)
    {
        $I->executeCommand('bootstrap --namespace Generated');

        $I->seeFileFound('codeception.yml');
        $I->seeInThisFile('namespace: Generated');
        $I->dontSeeInThisFile('namespace Generated\\');
        $this->checkFilesCreated($I);

        $I->seeFileFound('AcceptanceTester.php', 'tests/Support');
        $I->seeInThisFile('namespace Generated\\Support;');
    }

    public function bootstrapWithNamespaceShortcut(CliTester $I)
    {
        $I->executeCommand('bootstrap -s Generated');

        $I->seeFileFound('codeception.yml');
        $I->seeInThisFile('namespace: Generated');
        $I->dontSeeInThisFile('namespace Generated\\');
        $this->checkFilesCreated($I);

        $I->seeFileFound('AcceptanceTester.php', 'tests/Support');
        $I->seeInThisFile('namespace Generated\\Support;');
    }

    public function bootstrapWithActor(CliTester $I)
    {
        $I->executeCommand('bootstrap --actor Ninja');
        $I->seeFileFound('AcceptanceNinja.php', 'tests/Support/');
    }

    public function bootstrapEmpty(CliTester $I)
    {
        $I->executeCommand('bootstrap --empty');
        $I->dontSeeFileFound('tests/acceptance');
        $I->seeFileFound('codeception.yml');
    }

    public function bootstrapFromInit(CliTester $I)
    {
        $I->executeCommand('init bootstrap');
        $this->checkFilesCreated($I);
    }

    public function bootstrapFromInitUsingClassName(CliTester $I)
    {
        $I->executeCommand('init "Codeception\Template\Bootstrap"');
        $this->checkFilesCreated($I);
    }

    private function checkFilesCreated(CliTester $I)
    {
        $I->seeDirFound('tests/Support');
        $I->seeDirFound('tests/Support/Data');
        $I->seeDirFound('tests/_output');

        $I->seeFileFound('Functional.suite.yml', 'tests');
        $I->seeFileFound('Acceptance.suite.yml', 'tests');
        $I->seeFileFound('Unit.suite.yml', 'tests');

        $I->seeFileFound('AcceptanceTester.php', 'tests/Support');
        $I->seeFileFound('FunctionalTester.php', 'tests/Support');
        $I->seeFileFound('UnitTester.php', 'tests/Support');
    }
}
