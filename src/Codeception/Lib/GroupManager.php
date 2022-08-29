<?php

declare(strict_types=1);

namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\Test\Gherkin;
use Codeception\Test\Test;
use Codeception\Util\PathResolver;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function realpath;

/**
 * Loads information for groups from external sources (config, filesystem)
 */
class GroupManager
{
    protected array $testsInGroups = [];

    protected string $rootDir;

    public function __construct(protected array $configuredGroups)
    {
        $this->rootDir = Configuration::baseDir();
        $this->loadGroupsByPattern();
        $this->loadConfiguredGroupSettings();
    }

    /**
     * proceeds group names with asterisk:
     *
     * ```
     * "tests/_log/g_*" => [
     *      "tests/_log/group_1",
     *      "tests/_log/group_2",
     *      "tests/_log/group_3",
     * ]
     * ```
     */
    protected function loadGroupsByPattern(): void
    {
        foreach ($this->configuredGroups as $group => $pattern) {
            if (!str_contains($group, '*')) {
                continue;
            }
            $path = dirname($pattern);
            if (!PathResolver::isPathAbsolute($pattern)) {
                $path = $this->rootDir . $path;
            }

            $files = Finder::create()->files()
                ->name(basename($pattern))
                ->sortByName()
                ->in($path);

            foreach ($files as $file) {
                /** @var SplFileInfo $file * */
                $prefix = str_replace('*', '', $group);
                $pathPrefix = str_replace('*', '', basename($pattern));
                $groupName = $prefix . str_replace($pathPrefix, '', $file->getRelativePathname());

                $this->configuredGroups[$groupName] = dirname($pattern) . DIRECTORY_SEPARATOR . $file->getRelativePathname();
            }

            unset($this->configuredGroups[$group]);
        }
    }

    protected function loadConfiguredGroupSettings(): void
    {
        foreach ($this->configuredGroups as $group => $tests) {
            $this->testsInGroups[$group] = [];
            if (is_array($tests)) {
                foreach ($tests as $test) {
                    $file = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $test);
                    $this->testsInGroups[$group][] = $this->normalizeFilePath($file, $group);
                }
                continue;
            }

            $path = $tests;
            if (!codecept_is_path_absolute($tests)) {
                $path = $this->rootDir . $tests;
            }

            if (is_file($path)) {
                $handle = @fopen($path, "r");
                if ($handle) {
                    while (($test = fgets($handle, 4096)) !== false) {
                        // if the current line is blank then we need to move to the next line
                        // otherwise the current codeception directory becomes part of the group
                        // which causes every single test to run
                        if (trim($test) === '') {
                            continue;
                        }

                        $file = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], trim($test));
                        $this->testsInGroups[$group][] = $this->normalizeFilePath($file, $group);
                    }
                    fclose($handle);
                }
            }
        }
    }

    private function normalizeFilePath(string $file, string $group): string
    {
        $pathParts = explode(':', $file);
        if (codecept_is_path_absolute($file)) {
            if ($file[0] === '/' && count($pathParts) > 1) {
                //take segment before first :
                $this->checkIfFileExists($pathParts[0], $group);
                return sprintf('%s:%s', realpath($pathParts[0]), $pathParts[1]);
            } elseif (count($pathParts) > 2) {
                //on Windows take segment before second :
                $fullPath = $pathParts[0] . ':' . $pathParts[1];
                $this->checkIfFileExists($fullPath, $group);
                return sprintf('%s:%s', realpath($fullPath), $pathParts[2]);
            }

            $this->checkIfFileExists($file, $group);
            return realpath($file);
        } elseif (!str_contains($file, ':')) {
            $dirtyPath = $this->rootDir . $file;
            $this->checkIfFileExists($dirtyPath, $group);
            return realpath($dirtyPath);
        }

        $dirtyPath = $this->rootDir . $pathParts[0];
        $this->checkIfFileExists($dirtyPath, $group);
        return sprintf('%s:%s', realpath($dirtyPath), $pathParts[1]);
    }

    private function checkIfFileExists(string $path, string $group): void
    {
        if (!file_exists($path)) {
            throw new ConfigurationException('GroupManager: File or directory ' . $path . ' set in ' . $group . ' group does not exist');
        }
    }

    public function groupsForTest(Test $test): array
    {
        $filename = realpath($test->getFileName());
        $testName = $test->getName();
        $groups = $test->getMetadata()->getGroups();

        foreach ($this->testsInGroups as $group => $tests) {
            foreach ($tests as $testPattern) {
                if ($filename == $testPattern) {
                    $groups[] = $group;
                }

                if (str_starts_with($filename . ':' . $testName, (string)$testPattern)) {
                    $groups[] = $group;
                }
                if (
                    $test instanceof Gherkin
                    && mb_strtolower($filename . ':' . $test->getMetadata()->getFeature()) === mb_strtolower($testPattern)
                ) {
                    $groups[] = $group;
                }
            }
        }
        return array_unique($groups);
    }
}
