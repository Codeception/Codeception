<?php
use Snapshot\UserSnapshot;

class SnapshotFailCest
{
    public function loadSnapshot(DataTester $I, UserSnapshot $snapshot)
    {
        if (\Codeception\Util\Debug::isEnabled()) {
            $snapshot->shouldRefreshSnapshot(true);
        }
        $I->haveInDatabase('users', [
            'name' => 'hobgoblin',
            'email' => 'hobgoblin@vasya.com'
        ]);
        $snapshot->assert();
    }
}
