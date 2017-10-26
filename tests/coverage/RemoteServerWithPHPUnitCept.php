<?php

if (!class_exists('\PHP_CodeCoverage_Report_PHPUnit')) {
    $scenario->skip('XML output is not available in PHPUnit');
}

$I = new CoverGuy($scenario);
$I->wantTo('try generate remote codecoverage phpunit report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote_server --coverage-phpunit remote_server');
$I->seeFileFound('index.xml', 'tests/_output/remote_server');
