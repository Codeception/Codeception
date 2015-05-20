<?php
$scenario->group('core');

$I = new CliGuy($scenario);
$I->wantToTest('build command');
$I->runShellCommand('php codecept build');
$I->seeInShellOutput('generated successfully');
$I->seeFileFound('CodeGuy.php','tests/unit');
$I->seeFileFound('CliGuy.php','tests/cli');
$I->seeInThisFile('seeFileFound(');
