<?php
$I = new CoverGuy($scenario);
$I->wantTo('run advanced local code coverage for cest and test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run advanced_coverage CoverageTest --coverage', false, '-d pcov.directory=' . getcwd());
$I->seeInShellOutput('This test does not have a @covers annotation but is expected to have one');

$I->amGoingTo('run advanced  local codecoverage in cest');
$I->executeCommand('run advanced_coverage CoverageCest --coverage', false, '-d pcov.directory=' . getcwd());
$I->seeInShellOutput(' [SebastianBergmann\CodeCoverage\MissingCoversAnnotationException]');
