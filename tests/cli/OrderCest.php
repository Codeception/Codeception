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


    public function checkSimpleFiles(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order --no-exit --group simple');
        $I->seeFileFound('order.txt','tests/_log');
        $I->seeFileContentsEqual("BIBBBBSBSBS({[BST][BSTF][BST])}");
    }

    public function checkCestOrder(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/order/ReorderCest.php --no-exit');
        $I->seeFileFound('order.txt','tests/_log');
        $I->seeFileContentsEqual("BIB([B0123456])");
    }

    public function checkCodeceptionTest(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order CodeTest.php --no-exit');
        $I->seeFileFound('order.txt','tests/_log');
        $I->expect('global bootstrap, initialization, beforeSuite, beforeClass, bootstrap, before, after, afterSuite, afterClass');
        $I->seeFileContentsEqual("BI({B[C])}");
    }
}