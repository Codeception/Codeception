<?php

$I = new \Tests\Support\Step\GeneratorSteps($scenario);
$I->wantTo('generate helper');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:helper Db');
$I->seeFileWithGeneratedClass('Db', 'tests/_support/Helper');
$I->seeInThisFile('Db extends \Codeception\Module');
