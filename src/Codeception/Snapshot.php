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
        $path = $this->getFileName();
        if (!is_file($path)) {
            return;
        }

        $contents     = file_get_contents($path);
        $this->dataSet = $this->saveAsJson
            ? json_decode($contents, false, 512, JSON_THROW_ON_ERROR)
            : $contents;

        if ($this->dataSet === null || $this->dataSet === false) {
            throw new ContentNotFound('Loaded snapshot is empty');
        }
    }

    /**
     * Saves data set to file
     */
    protected function save(): void
    {
        $contents = $this->saveAsJson
            ? json_encode($this->dataSet, JSON_THROW_ON_ERROR)
            : $this->dataSet;

        file_put_contents($this->getFileName(), $contents);
    }

    /**
     * If no filename is defined, generates one from class name
     */
    protected function getFileName(): string
    {
        return codecept_data_dir() . ($this->fileName ??= preg_replace('#\W#', '.', static::class) . '.' . $this->extension);
    }

    /**
     * Performs assertion for data sets
     */
    public function assert(): void
    {
        $data = $this->fetchData();
        if (!$data) {
            throw new ContentNotFound('Fetched snapshot is empty.');
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

            $confirm = is_bool($this->refresh)
                ? $this->refresh
                : Debug::confirm('Should we update snapshot with fresh data? (Y/n) ');

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
        Debug::debug(static::class . ': ' . $message);
    }
}
