<?php
$I = new CliGuy($scenario);
$I->wantTo('generate sample Cept');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:cept dummy DummyCept');
$I->seeFileFound('DummyCept.php', 'tests/dummy');
$I->seeInThisFile('$I = new DumbGuy($scenario);');
$I->deleteThisFile();

$I->amGoingTo('create scenario in folder');
$I->executeCommand('generate:cept dummy path/DummyCept');
$I->seeFileFound('DummyCept.php', 'tests/dummy/path');
$I->deleteThisFile();

$I->amGoingTo('create file with Cept.php suffix');
$I->executeCommand('generate:cept dummy DummyCept.php');
$I->seeFileFound('DummyCept.php');
$I->deleteThisFile();

$I->amGoingTo('create file without any suffix');
$I->executeCommand('generate:cept dummy Dummy');
$I->seeFileFound('DummyCept.php');
