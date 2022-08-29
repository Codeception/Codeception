<?php

namespace Snapshot;

use DataTester;

class NotAJsonSnapshot extends \Codeception\Snapshot
{
    /**
     * @var string
     */
    public string $sourceFile;

    public function __construct(protected DataTester $dataTester)
    {
        $this->shouldSaveAsJson(false);
        $this->setSnapshotFileExtension('xml');
    }

    protected function fetchData(): array|string|false
    {
        return file_get_contents(codecept_data_dir($this->sourceFile));
    }
}
