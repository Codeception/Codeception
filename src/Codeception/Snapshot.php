<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Exception\ContentNotFound;
use Codeception\Util\Debug;
use Codeception\Util\Shared\Asserts;
use PHPUnit\Framework\AssertionFailedError;

abstract class Snapshot
{
    use Asserts;

    protected ?string $fileName = null;

    /**
     * @var string|false
     */
    protected $dataSet;

    protected ?bool $refresh = null;

    protected bool $showDiff = false;

    protected bool $saveAsJson = true;

    protected string $extension = 'json';

    /**
     * Should return data from current test run
     */
    abstract protected function fetchData(): array|string|false;

    /**
     * Performs assertion on saved data set against current dataset.
     * Can be overridden to implement custom assertion
     */
    protected function assertData(mixed $data): void
    {
        $this->assertSame($this->dataSet, $data, "Snapshot doesn't match real data");
    }

    /**
     * Loads data set from file.
     */
    protected function load(): void
    {
        if (!file_exists($this->getFileName())) {
            return;
        }
        $fileContents = file_get_contents($this->getFileName());
        if ($this->saveAsJson) {
            $fileContents = json_decode($fileContents, false, 512, JSON_THROW_ON_ERROR);
        }
        $this->dataSet = $fileContents;
        if (!$this->dataSet) {
            throw new ContentNotFound("Loaded snapshot is empty");
        }
    }

    /**
     * Saves data set to file
     */
    protected function save(): void
    {
        $fileContents = $this->dataSet;
        if ($this->saveAsJson) {
            $fileContents = json_encode($fileContents, JSON_THROW_ON_ERROR);
        }
        file_put_contents($this->getFileName(), $fileContents);
    }

    /**
     * If no filename is defined, generates one from class name
     */
    protected function getFileName(): string
    {
        if (!$this->fileName) {
            $this->fileName = preg_replace('#\W#', '.', $this::class) . '.' . $this->extension;
        }
        return codecept_data_dir() . $this->fileName;
    }

    /**
     * Performs assertion for data sets
     */
    public function assert(): void
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
     */
    public function shouldRefreshSnapshot(bool $refresh = true): void
    {
        $this->refresh = $refresh;
    }

    /**
     * Show detailed diff if snapshot test fails
     */
    public function shouldShowDiffOnFail(bool $showDiff = true): void
    {
        $this->showDiff = $showDiff;
    }

    /**
     * json_encode/json_decode the snapshot data on storing/reading.
     */
    public function shouldSaveAsJson(bool $saveAsJson = true): void
    {
        $this->saveAsJson = $saveAsJson;
    }

    /**
     * Set the snapshot file extension.
     * By default it will be stored as `.json`.
     *
     * The file extension will not perform any formatting in the data,
     * it is only used as the snapshot file extension.
     */
    public function setSnapshotFileExtension(string $fileExtension = 'json'): void
    {
        $this->extension = $fileExtension;
    }

    private function printDebug(string $message): void
    {
        Debug::debug($this::class . ': ' . $message);
    }
}
