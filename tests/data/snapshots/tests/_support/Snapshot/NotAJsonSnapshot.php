<?php
namespace Snapshot;

use DataTester;

class NotAJsonSnapshot extends \Codeception\Snapshot
{
    protected DataTester $dataTester;

    /**
     * @var string
     */
    public string $sourceFile;

    public function __construct(DataTester $I)
    {
        $this->dataTester = $I;
        $this->shouldSaveAsJson(false);
        $this->setSnapshotFileExtension('xml');
    }

    protected function fetchData()
    {
        return file_get_contents(codecept_data_dir($this->sourceFile));
    }
}
