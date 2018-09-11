<?php

use Snapshot\UserSnapshot;

class SnapshotDataCest
{
    public function loadSnapshot(UserSnapshot $snapshot)
    {
        $snapshot->assert();
    }

    public function loadSnapshotAndSkipRefresh(DataTester $I, UserSnapshot $snapshot)
    {
        $snapshot->assert();
        $I->haveInDatabase('users', [
            'name' => 'hobgoblin',
            'email' => 'hobgoblin@vasya.com'
        ]);

        $snapshot->shouldRefreshSnapshot(false);
        $I->expectException(\PHPUnit\Framework\AssertionFailedError::class, function() use ($snapshot) {
            $snapshot->assert();
        });
    }

    public function loadSnapshotAndRefresh(DataTester $I, UserSnapshot $snapshot)
    {
        $snapshot->assert();
        $I->haveInDatabase('users', [
            'name' => 'hobgoblin',
            'email' => 'hobgoblin@vasya.com'
        ]);

        $snapshot->shouldRefreshSnapshot(true);
        $snapshot->assert();
    }

}
