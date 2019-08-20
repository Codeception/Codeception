<?php
$I = new CliGuy($scenario);
$I->wantTo('generate test scenario');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:scenarios dummy');
$I->seeFileFound('File_Exists.txt', 'tests/_data/scenarios');
$I->seeFileContentsEqual(<<<EOF
I WANT TO CHECK CONFIG EXISTS

I see file found "\$codeception"


EOF
);
