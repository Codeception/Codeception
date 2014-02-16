<?php 
$I = new CliGuy($scenario);
$I->wantTo('change configs and check that Guy is rebuilt');
$I->amInPath('tests/data/sandbox');
$I->writeToFile('tests/unit.suite.yml', <<<EOF
class_name: CodeGuy
modules:
    enabled: [Cli, CodeHelper]
EOF
);
$I->executeCommand('run unit PassingTest.php --debug');
$I->seeInShellOutput('Cli');
$I->seeFileFound('tests/unit/CodeGuy.php');
$I->seeInThisFile('public function seeInShellOutput');
$I->seeInThisFile('public function runShellCommand');
