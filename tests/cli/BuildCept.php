<?php
$scenario->group('core');

$I = new CliGuy($scenario);
$I->wantToTest('build command');
$I->runShellCommand('php codecept build');
$I->seeInShellOutput('generated successfully');
$I->seeFileFound('CodeGuy.php','tests/_support');
$I->seeFileFound('CliGuy.php','tests/_support');
$I->seeInThisFile('seeFileFound(');
