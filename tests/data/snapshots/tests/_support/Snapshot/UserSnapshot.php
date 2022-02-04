<?php

namespace Snapshot;

use Codeception\Snapshot;
use DataTester;

class UserSnapshot extends Snapshot
{
    public function __construct(protected DataTester $dataTester)
    {
    }

    protected function fetchData(): array|string|false
    {
        return $this->dataTester->grabColumnFromDatabase('users', 'email');
    }
}
