<?php
$I = new CliGuy\GeneratorSteps($scenario);
$I->wantTo('generate step object');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:stepobject dummy Login --silent');
$I->seeFileWithGeneratedClass('Login', 'tests/_support/Step/Dummy');
$I->seeInThisFile('Login extends \DumbGuy');
