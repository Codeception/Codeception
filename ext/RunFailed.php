<?php

declare(strict_types=1);

namespace Codeception\Extension;

use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;

use function array_diff_key;
use function array_filter;
use function array_key_exists;
use function file;
use function file_exists;
use function file_put_contents;
use function implode;
use function is_file;
use function preg_replace;
use function realpath;
use function str_replace;
use function strlen;
use function substr;
use function unlink;

/**
 * Saves failed tests into `tests/_output/failed` in order to rerun failed tests.
 *
 * To rerun failed tests just run the `failed` group:
 *
 * ```
 * php codecept run -g failed
 * ```
 *
 * To change failed group name add:
 * ```
 * --override "extensions: config: Codeception\Extension\RunFailed: fail-group: another_group1"
 * ```
 * Remember: If you run tests and they generated custom-named fail group, to run this group, you should add override too
 *
 * **This extension is enabled by default.**
 *
 * ``` yaml
 * extensions:
 *     enabled: [Codeception\Extension\RunFailed]
 * ```
 *
 * On each execution failed tests are logged and saved into `tests/_output/failed` file.
 * Tests whose files were deleted in the meantime are removed from this file before the next run.
 */
class RunFailed extends Extension
{
    /**
     * @var array<string, string>
     */
    public static array $events = [
        Events::RESULT_PRINT_AFTER => 'saveFailed'
    ];

    /** @var string filename/groupname for failed tests */
    protected string $group = 'failed';

    public function _initialize(): void
    {
        if (array_key_exists('fail-group', $this->config) && $this->config['fail-group']) {
            $this->group = $this->config['fail-group'];
        }
        $this->removeDeletedTests();
        $logPath = str_replace($this->getRootDir(), '', $this->getLogDir()); // get local path to logs
        $this->_reconfigure(['groups' => [$this->group => $logPath . $this->group]]);
    }

    public function saveFailed(PrintResultEvent $event): void
    {
        $file = $this->getLogDir() . $this->group;
        $result = $event->getResult();
        if ($result->wasSuccessful()) {
            if (is_file($file)) {
                unlink($file);
            }
            return;
        }
        $output = [];
        foreach ($result->failures() as $fail) {
            $output[] = $this->localizePath(Descriptor::getTestFullName($fail->getTest()));
        }
        foreach ($result->errors() as $fail) {
            $output[] = $this->localizePath(Descriptor::getTestFullName($fail->getTest()));
        }

        file_put_contents($file, implode("\n", $output));
    }

    /**
     * Removes tests whose files were deleted since the last run, as the group could not be loaded otherwise.
     */
    private function removeDeletedTests(): void
    {
        $file = $this->getLogDir() . $this->group;
        if (!is_file($file)) {
            return;
        }
        $tests = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $deletedTests = array_filter($tests, fn (string $test): bool => !file_exists($this->getTestFilePath($test)));
        if ($deletedTests === []) {
            return;
        }
        foreach ($deletedTests as $test) {
            $this->writeln("[RunFailed] Removed deleted test from {$this->group} group: <info>{$test}</info>");
        }
        $tests = array_diff_key($tests, $deletedTests);
        if ($tests === []) {
            unlink($file);
            return;
        }
        file_put_contents($file, implode("\n", $tests));
    }

    private function getTestFilePath(string $test): string
    {
        // strip the test name, but keep the drive letter of absolute Windows paths
        $path = (string) preg_replace('#(?<!^[A-Za-z]):.*$#', '', trim($test));
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        return codecept_is_path_absolute($path) ? $path : $this->getRootDir() . $path;
    }

    protected function localizePath(string $path): string
    {
        $root = realpath($this->getRootDir()) . DIRECTORY_SEPARATOR;
        if (str_starts_with($path, $root)) {
            return substr($path, strlen($root));
        }
        return $path;
    }
}
