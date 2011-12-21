<?php
$I = new CliGuy($scenario);
$I->wantToTest('build command');
$I->runShellCommmand('php codecept build');
$I->seeInShellOutput('generated sucessfully');
$I->seeFileFound('TestGuy.php','tests/functional');
$I->seeFileFound('CodeGuy.php','tests/unit');
$I->seeFileFound('CliGuy.php','tests/acceptance');
$I->seeInFile('seeFileFound(');
