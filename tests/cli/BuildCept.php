<?php
$scenario->group('core');

$I = new CliGuy($scenario);
$I->wantToTest('build command');
$I->runShellCommand('php codecept build');
$I->seeInShellOutput('generated successfully');
$I->seeFileFound('CodeGuy.php','tests/support');
$I->seeFileFound('CliGuy.php','tests/support');
$I->seeInThisFile('class CliGuy extends \Codeception\Actor');
$I->seeInThisFile('use _generated\CliGuyActions');
$I->seeFileFound('CliGuyActions.php','tests/support/_generated');
$I->seeInThisFile('seeFileFound(');
