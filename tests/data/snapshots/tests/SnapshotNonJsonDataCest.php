<?php

use Snapshot\NotAJsonSnapshot;

class SnapshotNonJsonDataCest
{
    public function loadSnapshot(DataTester $I, NotAJsonSnapshot $snapshot)
    {
        $snapshot->sourceFile = 'dummy_xml.original.xml';
        $snapshot->assert();
    }

    public function loadSnapshotAndSkipRefresh(DataTester $I, NotAJsonSnapshot $snapshot)
    {
        $snapshot->sourceFile = 'dummy_xml.original.xml';
        $snapshot->assert();

        $snapshot->sourceFile = 'dummy_xml.updated.xml';
        $snapshot->shouldRefreshSnapshot(false);
        $I->expectThrowable(\PHPUnit\Framework\AssertionFailedError::class, function () use ($snapshot) {
            $snapshot->assert();
        });
    }

    public function loadSnapshotAndRefresh(DataTester $I, NotAJsonSnapshot $snapshot)
    {
        $snapshot->sourceFile = 'dummy_xml.original.xml';
        $snapshot->assert();

        $snapshot->sourceFile = 'dummy_xml.updated.xml';
        $snapshot->shouldRefreshSnapshot(true);
        $snapshot->assert();

        $I->assertEquals(
            file_get_contents(codecept_data_dir($snapshot->sourceFile)),
            file_get_contents(codecept_data_dir('Snapshot.NotAJsonSnapshot.xml')),
            'Snapshot stored data must be identical to source.'
        );
    }
}
