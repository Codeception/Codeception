<?php
$I = new CoverGuy($scenario);
$I->wantTo('try generate remote codecoverage xml report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote --coverage-xml');
$I->seeInShellOutput('Code Coverage Report');
$I->seeInShellOutput(<<<EOF
index
  Methods:  50.00% ( 1/ 2)   Lines:  50.00% (  2/  4)
EOF
);
$I->seeFileFound('coverage.xml','tests/_log');

