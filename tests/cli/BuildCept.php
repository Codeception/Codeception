<?php
$scenario->group('core');

$I = new CliGuy($scenario);
$I->wantToTest('build command');
$I->runShellCommand('php codecept build');
$I->seeInShellOutput('generated successfully');
$I->seeInSupportDir('CodeGuy.php');
$I->seeInSupportDir('CliGuy.php');
$I->seeInThisFile('class CliGuy extends \Codeception\Actor');
$I->seeInThisFile('use _generated\CliGuyActions');
$I->seeFileFound('CliGuyActions.php','tests/support/_generated');
$I->seeInThisFile('seeFileFound(');
