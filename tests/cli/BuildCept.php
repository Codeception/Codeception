<?php
$I = new CliGuy($scenario);
$I->wantToTest('build command');
$I->runShellCommmand('php codecept build');
$I->seeInShellOutput('generated sucessfully');
$I->seeFileFound('CodeGuy.php','tests/unit');
$I->seeFileFound('CliGuy.php','tests/acceptance');
$I->seeInThisFile('seeFileFound(');
