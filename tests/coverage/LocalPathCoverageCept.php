<?php

/**
 * @skip Path coverage is broken since symfony/console 5.4.16, 6.0.16, 6.1.8 and 6.2.0
 * https://bugs.xdebug.org/view.php?id=2108
 */

$I = new CoverGuy($scenario);
$I->wantTo('measure path coverage');
$I->amGoingTo('run local code coverage with path and branch coverage');
$I->executeCommand("run -o 'coverage: path_coverage: true' math MathCest --coverage", false);
$I->canSeeInShellOutput('Paths:   66.67%');
$I->canSeeInShellOutput('Branches:   80.00%');
