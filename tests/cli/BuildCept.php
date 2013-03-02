<?php
$I = new CliGuy($scenario);
$I->wantToTest('build command');
$I->runShellCommmand('php codecept build');
$I->seeInShellOutput('generated successfully');
$I->seeFileFound('CodeGuy.php','tests/unit');
$I->seeFileFound('CliGuy.php','tests/cli');
$I->seeInThisFile('seeFileFound(');
