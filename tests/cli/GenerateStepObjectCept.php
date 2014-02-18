<?php
$I = new CliGuy\GeneratorSteps($scenario);
$I->wantTo('generate step object');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:stepobject dummy Login --silent');
$I->seeFileWithGeneratedClass('LoginSteps','tests/dummy/_steps');
$I->seeInThisFile('LoginSteps extends \DumbGuy');
$I->seeAutoloaderWasAdded('Steps','tests/dummy');
