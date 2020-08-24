<?php
namespace Snapshot;

class NotAJsonSnapshot extends \Codeception\Snapshot
{
    /**
     * @var DataTester
     */
    protected $dataTester;

    /**
     * @var string
     */
    public $sourceFile;

    public function __construct(\DataTester $I)
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
