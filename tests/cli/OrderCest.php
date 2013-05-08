<?php

class OrderCest {

    public function checkOneFile(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order LoadingOrderCept.php');
        $I->expect('initialization, bootstrap(B), beforeSuite, before, bootstrap(B), test(T), after, afterSuite');
        $I->seeFileFound('order.txt','tests/_log');
        $I->seeInThisFile("IBS([BST])");
    }

    public function checkForFails(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order FailedCept.php --no-exit');
        $I->seeFileFound('order.txt','tests/_log');
        $I->expect('initialization, bootstrap, beforeSuite, before, bootstrap, test, after, fail, afterSuite');
        $I->seeInThisFile("IBS([BSTF])");
    }


    public function checkTwoFiles(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order --no-exit');
        $I->seeFileFound('order.txt','tests/_log');
        $I->seeInThisFile("IBSBSBS([BST][BSTF][BST])");
    }


}