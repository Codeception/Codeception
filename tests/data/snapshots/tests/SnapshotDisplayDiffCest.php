<?php

use Snapshot\UserSnapshot;

class SnapshotDisplayDiffCest
{
    public function snapshotCanDisplayDiff(DataTester $I, UserSnapshot $snapshot)
    {
        $snapshot->assert();

        $expected = $I->grabColumnFromDatabase('users', 'email');
        $actual = array_merge($expected, ['hobgoblin@vasya.com']);

        $I->haveInDatabase('users', [
           'name' => 'hobgoblin',
           'email' => 'hobgoblin@vasya.com'
        ]);

        $snapshot->shouldRefreshSnapshot(false);
        try {
            $snapshot->shouldShowDiffOnFail();
            $snapshot->assert();
            $I->fail('Snapshot assert must throw an exception.');
        } catch (\PHPUnit\Framework\ExpectationFailedException $t) {
            $I->assertEquals($expected, $t->getComparisonFailure()->getExpected());
            $I->assertEquals($actual, $t->getComparisonFailure()->getActual());
        } catch (Throwable $t) {
            $I->fail('Snapshot assert must throw "\PHPUnit\Framework\ExpectationFailedException"');
        }
    }
}
