<?php

class OrderCest
{
    public function checkOneFile(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order LoadingOrderCept.php');
        $I->expect('global bootstrap, initialization, beforeSuite, before, bootstrap(B), test(T), after, afterSuite');
        $I->seeFileFound('order.txt','tests/_output');
        $I->seeFileContentsEqual("BIB([ST])");
    }

    public function checkForFails(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order FailedCept.php --no-exit');
        $I->seeFileFound('order.txt','tests/_output');
        $I->expect('global bootstrap, initialization, beforeSuite, before, bootstrap, test, fail, after, afterSuite');
        $I->seeFileContentsEqual("BIB([STF])");
    }


    public function checkSimpleFiles(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order --no-exit --group simple');
        $I->seeFileFound('order.txt','tests/_output');
        $I->seeFileContentsEqual("BIB({{{[ST][STF][ST])}}}");
    }

    public function checkCestOrder(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/order/ReorderCest.php --no-exit');
        $I->seeFileFound('order.txt','tests/_output');
        $I->seeFileContentsEqual("BIB([0123456])");
    }

    public function checkFailingCestOrder(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/order/FailedCest.php --no-exit -vvv');
        $I->seeFileFound('order.txt','tests/_output');
        $I->seeFileContentsEqual("BIB([a%F])");
    }

    public function checkCodeceptionTest(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order CodeTest.php --no-exit');
        $I->seeFileFound('order.txt','tests/_output');
        $I->expect('
            global bootstrap,
            initialization,
            beforeSuite,
            beforeClass,
            @beforeClass,
            bootstrap,
            before,
            @before
            test,
            after,
            @after,
            afterSuite,
            afterClass,
            @afterClass');
        $I->seeFileContentsEqual("BIB({{[<C]>)}}");
    }

    public function checkAfterBeforeClassInTests(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order BeforeAfterClassTest.php');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->seeInThisFile('BIB({[1][2])}');
    }
}