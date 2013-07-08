<?php
$I = new CliGuy($scenario);
$I->wantTo('see that comments can be easily added to test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run tests/skipped/CommentsCept.php --steps');
$I->seeInShellOutput('* As a very lazy qa');
$I->seeInShellOutput('* So that I not do anything at all');
$I->seeInShellOutput('* have a comment for you');
$I->seeInShellOutput('* but I am too lazy to do any job');
$I->seeInShellOutput('* so please do that yourself');
$I->seeInShellOutput('I am going to leave that to you');
$I->seeInShellOutput('I expect you forgive me');

