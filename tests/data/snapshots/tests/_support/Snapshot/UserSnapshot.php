<?php

namespace Snapshot;

use Codeception\Snapshot;
use DataTester;

class UserSnapshot extends Snapshot
{
    /**
     * @var DataTester
     */
    protected $dataTester;

    public function __construct(DataTester $I)
    {
        $this->dataTester = $I;
    }

    protected function fetchData()
    {
        return $this->dataTester->grabColumnFromDatabase('users', 'email');
    }
}