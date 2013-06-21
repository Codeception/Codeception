<?php

class OrderCest {

    public function checkOneFile(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order LoadingOrderCept.php');
        $I->expect('global bootstrap, initialization, bootstrap(B), beforeSuite, before, bootstrap(B), test(T), after, afterSuite');
        $I->seeFileFound('order.txt','tests/_log');
        $I->seeFileContentsEqual("BIBS([BST])");
    }

    public function checkForFails(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order FailedCept.php --no-exit');
        $I->seeFileFound('order.txt','tests/_log');
        $I->expect('global bootstrap, initialization, bootstrap, beforeSuite, before, bootstrap, test, fail, after, afterSuite');
        $I->seeFileContentsEqual("BIBS([BSTF])");
    }


    public function checkTwoFiles(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order --no-exit');
        $I->seeFileFound('order.txt','tests/_log');
        $I->seeFileContentsEqual("BIBSBSBS([BST][BSTF][BST])");
    }


}