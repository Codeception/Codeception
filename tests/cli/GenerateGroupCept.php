<?php
$I = new CliGuy\GeneratorSteps($scenario);
$I->wantTo('generate new group');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:group core');
$I->seeFileWithGeneratedClass('CoreGroup','tests/_groups');
$I->seeInThisFile("static \$group = 'core'");
$I->dontSeeInThisFile('public function _before(\Codeception\Event\Test \$e)');
$I->seeFileFound('tests/_bootstrap.php');
$I->seeInThisFile("\\Codeception\\Util\\Autoload::registerSuffix('Group', __DIR__.DIRECTORY_SEPARATOR.'_groups'");
