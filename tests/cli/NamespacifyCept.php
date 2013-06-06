<?php
$I = new CliGuy($scenario);
$I->wantTo('namespacify current suite');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('namespacify Jazz --force');
$I->seeFileFound('codeception.yml');
$I->seeInThisFile('namespace: Jazz');
$I->seeFileFound('OrderHelper.php');
$I->seeInThisFile('namespace Jazz\Codeception\Module;');
$I->seeFileFound('FileExistsCept.php');
$I->seeInThisFile('use Jazz\Codeception\DumbGuy;');

