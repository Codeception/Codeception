<?php
if (strpos(PHP_VERSION, '5.3')===0) $this->markTestSkipped();

$I = new CliGuy($scenario);
$I->wantTo('try generate remote codecoverage xml report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote --coverage --html');
$I->seeFileFound('index.html','tests/_log/coverage');

