<?php
namespace Helper;

use Codeception\TestInterface;

class RestoreData extends \Codeception\Module
{
    public function _before(TestInterface $test)
    {
        copy(codecept_data_dir() . 'Snapshot.UserSnapshot.original.json', codecept_data_dir() . 'Snapshot.UserSnapshot.json');
        copy(codecept_data_dir() . 'snapshot.test_db_snapshot', codecept_data_dir() . 'snapshot_test.db');
    }
}