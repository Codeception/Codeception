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
        $this->dataSet = json_decode(file_get_contents($this->getFileName()));
        if (!$this->dataSet) {
            throw new ContentNotFound("Loaded snapshot is empty");
        }
    }

    /**
     * Saves data set to file
     */
    protected function save()
    {
        file_put_contents($this->getFileName(), json_encode($this->dataSet));
    }

    /**
     * If no filename is defined, generates one from class name
     *
     * @return string
     */
    protected function getFileName()
    {
        if (!$this->fileName) {
            $this->fileName = preg_replace('/\W/', '.', get_class($this)) . '.json';
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

    private function printDebug($message)
    {
        Debug::debug(get_class($this) . ': ' . $message);
    }
}
