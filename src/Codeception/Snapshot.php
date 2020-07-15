<?php

namespace Codeception;

use Codeception\Exception\ContentNotFound;
use Codeception\Util\Debug;
use Codeception\Util\Shared\Asserts;
use PHPUnit\Framework\AssertionFailedError;

abstract class Snapshot
{
    use Asserts;

    protected $fileName;

    protected $dataSet;

    protected $refresh;

    protected $showDiff = false;

    protected $saveAsJson = true;

    protected $extension = 'json';

    /**
     * Should return data from current test run
     *
     * @return mixed
     */
    abstract protected function fetchData();

    /**
     * Performs assertion on saved data set against current dataset.
     * Can be overridden to implement custom assertion
     *
     * @param $data
     */
    protected function assertData($data)
    {
        $this->assertEquals($this->dataSet, $data, 'Snapshot doesn\'t match real data');
    }

    /**
     * Loads data set from file.
     */
    protected function load()
    {
        if (!file_exists($this->getFileName())) {
            return;
        }
        $fileContents = file_get_contents($this->getFileName());
        if ($this->saveAsJson) {
            $fileContents = json_decode($fileContents);
        }
        $this->dataSet = $fileContents;
        if (!$this->dataSet) {
            throw new ContentNotFound("Loaded snapshot is empty");
        }
    }

    /**
     * Saves data set to file
     */
    protected function save()
    {
        $fileContents = $this->dataSet;
        if ($this->saveAsJson) {
            $fileContents = json_encode($fileContents);
        }
        file_put_contents($this->getFileName(), $fileContents);
    }

    /**
     * If no filename is defined, generates one from class name
     *
     * @return string
     */
    protected function getFileName()
    {
        if (!$this->fileName) {
            $this->fileName = preg_replace('/\W/', '.', get_class($this)) . '.' . $this->extension;
        }
        return codecept_data_dir() . $this->fileName;
    }

    /**
     * Performs assertion for data sets
     */
    public function assert()
    {
        // fetch data
        $data = $this->fetchData();
        if (!$data) {
            throw new ContentNotFound("Fetched snapshot is empty.");
        }

        $this->load();

        if (!$this->dataSet) {
            $this->printDebug('Snapshot is empty. Updating snapshot...');
            $this->dataSet = $data;
            $this->save();
            return;
        }

        try {
            $this->assertData($data);
            $this->printDebug('Data matches snapshot');
        } catch (AssertionFailedError $exception) {
            $this->printDebug('Snapshot assertion failed');

            if (!is_bool($this->refresh)) {
                $confirm = Debug::confirm('Should we update snapshot with fresh data? (Y/n) ');
            } else {
                $confirm = $this->refresh;
            }

            if ($confirm) {
                $this->dataSet = $data;
                $this->save();
                $this->printDebug('Snapshot data updated');
                return;
            }

            if ($this->showDiff) {
                throw $exception;
            }

            $this->fail($exception->getMessage());
        }
    }

    /**
     * Force update snapshot data.
     *
     * @param bool $refresh
     */
    public function shouldRefreshSnapshot($refresh = true)
    {
        $this->refresh = $refresh;
    }

    /**
     * Show detailed diff if snapshot test fails
     *
     * @param bool $showDiff
     */
    public function shouldShowDiffOnFail($showDiff = true)
    {
        $this->showDiff = $showDiff;
    }

    /**
     * json_encode/json_decode the snapshot data on storing/reading.
     *
     * @param bool $saveAsJson
     */
    public function shouldSaveAsJson($saveAsJson = true)
    {
        $this->saveAsJson = $saveAsJson;
    }

    /**
     * Set the snapshot file extension.
     * By default it will be stored as `.json`.
     *
     * The file extension will not perform any formatting in the data,
     * it is only used as the snapshot file extension.
     *
     * @param string $fileExtension
     * @return void
     */
    public function setSnapshotFileExtension($fileExtension = 'json')
    {
        $this->extension = $fileExtension;
    }

    private function printDebug($message)
    {
        Debug::debug(get_class($this) . ': ' . $message);
    }
}
