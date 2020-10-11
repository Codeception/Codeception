<?php
if (!class_exists(\SebastianBergmann\CodeCoverage\Report\Cobertura::class)) {
    $scenario->skip('Cobertura report requires php-code-coverage 9.2');
}
$I = new CoverGuy($scenario);
$I->wantTo('try generate remote cobertura xml report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote --coverage-cobertura');
$I->seeInShellOutput('Cobertura report generated in cobertura.xml');
$I->seeFileFound('cobertura.xml', 'tests/_output');

