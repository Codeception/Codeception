<?php
namespace Snapshot;

class UserSnapshot extends \Codeception\Snapshot
{
    /**
     * @var DataTester
     */
    protected $dataTester;

    public function __construct(\DataTester $I)
    {
        $this->dataTester = $I;
    }

    protected function fetchData()
    {
        return $this->dataTester->grabColumnFromDatabase('users', 'email');
    }
}