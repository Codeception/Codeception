<?php
namespace Codeception\Module;

use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Module as CodeceptionModule;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\PartedModule;

/**
 * Module for loading and preparing Stubs and other files
 *
 * This module can be used to load files from the "_data" folder of the test environment. Further, these stubs may be
 * automatically "prepared" (e.g., replace placeholders) in order to use the latter for communicating with Web Services
 * or other modules.
 *
 * ## Configuration
 *
 * No specific Configuration is needed
 *
 * ### Example
 *
 *     modules:
 *        enabled:
 *            - StubWorker
 *
 * ## Conflicts
 *
 * No Conflicts are known
 */
class StubWorker extends CodeceptionModule implements DependsOnModule, PartedModule, ConflictsWithModule
{

    protected $dependencyMessage = <<<EOF
Example enabling the StubWorker in a Test Suite
--
modules:
    enabled:
        - StubWorker
--
EOF;


    /**
     * Returns class name or interface of module which can conflict with current.
     *
     * @return string
     */
    public function _conflicts()
    {
        return '';
    }

    /**
     * Specifies class or module which is required for current one.
     *
     * THis method should return array with key as class name and value as error message
     * [className => errorMessage
     * ]
     *
     * @return mixed
     */
    public function _depends()
    {
        return [];
    }

    public function _parts()
    {
        return [];
    }

    /**
     * Loads a File from the DATA directory and replaces specific placeholders
     *
     * @param string $fileName the stub file to be loaded (relative to the DATA directory)
     * @param array  $vars     key/value for replacements
     * @param string $startPattern
     * @param string $endPattern
     *
     * @return mixed|string
     */
    public function loadAndPrepareStub($fileName, array $vars = [], $startPattern = '{{', $endPattern = '}}')
    {
        // repeat our patterns
        $startPatterns = array_fill(0, count($vars), $startPattern);
        $endPatterns = array_fill(0, count($vars), $endPattern);

        // load the stub and replace the placeholders
        $stub = $this->_loadFileFromDataDirectory($fileName);
        $stub = str_replace(array_map([$this, 'maskStubVariables'], array_keys($vars), $startPatterns, $endPatterns), array_values($vars), $stub);

        return $stub;
    }

    /**
     * Load a given file from the "data" directory defined in the suite
     *
     * @param $filename
     *
     * @return string
     */
    public function _loadFileFromDataDirectory($filename)
    {
        // get the absolute path on disk for this codeception instance
        $filePath = codecept_data_dir() . $filename;

        $this->checkIfFileExists($filePath);

        $stub = file_get_contents($filePath);
        return $stub;
    }

    /**
     *
     * Checks, if the given file exists and is readable.
     *
     * This method is used, e.g., in the attachFile() method to append the file to a request (e.g., for uploading)
     *
     * @param $filePath
     *
     * @throws \InvalidArgumentException
     */
    private function checkIfFileExists($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File does not exist: $filePath");
        }

        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("File is not readable: $filePath");
        }
    }

    /**
     * Masks variables in the data files (stubs)
     *
     * @param        $key
     * @param string $startPattern
     * @param string $endPattern
     *
     * @return string
     */
    private function maskStubVariables($key, $startPattern = '{{', $endPattern = '}}')
    {
        return $startPattern . $key . $endPattern;
    }

}
