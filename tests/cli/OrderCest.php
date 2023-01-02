<?php

declare(strict_types=1);

final class OrderCest
{
    public function checkOneFile(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order LoadingOrderCept.php');
        $I->expect('global bootstrap, initialization, beforeSuite, before, bootstrap(B), test(T), after, afterSuite');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->seeFileContentsEqual("BIB([ST])");
    }

    public function checkForFails(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order FailedCept.php --no-exit');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->expect('global bootstrap, initialization, beforeSuite, before, bootstrap, test, fail, after, afterSuite');
        $I->seeFileContentsEqual("BIB([STF])");
    }

    public function checkForCanCantFails(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order CanCantFailCept.php --no-exit');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->expect(
            'global bootstrap, initialization, beforeSuite, before, bootstrap, test,'
            . ' test, fail, after, afterSuite'
        );
        $I->seeFileContentsEqual("BIB([STTF])");
    }

    public function checkForCanCantFailsInCest(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order CanCantFailCest.php --no-ansi --no-exit');
        $I->seeInShellOutput('x CanCantFailCest: Test one [F]');
        $I->seeInShellOutput('x CanCantFailCest: Test two [F]');
        $I->dontSeeInShellOutput('+ CanCantFailCest: One');
        $I->dontSeeInShellOutput('+ CanCantFailCest: Two');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->expect(
            'global bootstrap, initialization, beforeSuite, before, bootstrap, test,'
            . ' test, fail, test, test, fail, after, afterSuite'
        );
        $I->seeFileContentsEqual("BIB([TTF][TTF])");
    }

    public function checkForCanCantFailsInTest(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order CanCantFailTest.php --no-ansi --no-exit');
        $I->seeInShellOutput('x CanCantFailTest: One');
        $I->seeInShellOutput('x CanCantFailTest: Two');
        $I->dontSeeInShellOutput('+ CanCantFailTest: One');
        $I->dontSeeInShellOutput('+ CanCantFailTest: Two');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->expect(
            'global bootstrap, initialization, beforeSuite, before, bootstrap, test,'
            . ' test, fail, test, test, fail, after, afterSuite'
        );
        $I->seeFileContentsEqual("BIB([TTF][TTF])");
    }

    public function checkSimpleFiles(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order --no-exit --group simple');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->seeFileContentsEqual("BIBP([ST][STTF][STF][ST])");
    }

    public function checkCestOrder(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/order/ReorderCest.php --no-exit');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->seeFileContentsEqual("BIB([0123456])");
    }

    public function checkFailingCestOrder(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/order/FailedCest.php --no-exit -vvv');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->seeFileContentsEqual("BIB([a%F])");
    }

    public function checkCodeceptionTest(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order CodeTest.php --no-exit');
        $I->seeFileFound('order.txt', 'tests/_output');
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
        $I->seeFileContentsEqual("BIB({{[<C>]}})");
    }

    public function checkAfterBeforeClassInTests(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order BeforeAfterClassTest.php');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->seeInThisFile('BIB({[1][2]})');
    }

    public function checkAfterBeforeClassInTestWithDataProvider(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order BeforeAfterClassWithDataProviderTest.php');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->seeInThisFile('BIB({[A][B][C]})');
    }

    public function checkBootstrapIsLoadedBeforeTests(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run order ParsedLoadedTest.php');
        $I->seeFileFound('order.txt', 'tests/_output');
        # Module before and after hooks are executed for unit tests now
        $I->seeInThisFile('BIBP([T])');
    }

    public function checkAfterBeforeHooksAreExecutedOnlyOnce(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run math,order,scenario,skipped :BeforeAfterClassTest');
        $I->seeFileFound('order.txt', 'tests/_output');
        $I->seeInThisFile('BIBP({[1][2]})');
    }
}
