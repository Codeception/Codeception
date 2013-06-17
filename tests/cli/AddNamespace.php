<?php
$I = new CliGuy($scenario);
$I->wantTo('add namespace to configuration');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('refactor:add-namespace Jazz --force');
$I->seeFileFound('codeception.yml');
$I->seeInThisFile('namespace: Jazz');
$I->seeFileFound('OrderHelper.php');
$I->seeInThisFile('namespace Jazz\Codeception\Module;');
$I->seeFileFound('FileExistsCept.php');
$I->seeInThisFile('use Jazz\Codeception\DumbGuy;');

