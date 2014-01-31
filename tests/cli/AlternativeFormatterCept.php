<?php
$I = new CliGuy($scenario);
$I->wantTo('use alternative formatter delivered through extensions');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run tests/dummy/FileExistsCept.php -c codeception_extended.yml');
$I->dontSeeInShellOutput("Trying to check config");
$I->seeInShellOutput('[+] check config');

