<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Descriptor;
use Codeception\TestInterface;
use Codeception\Test\Gherkin;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Loads information for groups from external sources (config, filesystem)
 */
class GroupManager
{
    protected $configuredGroups;
    protected $testsInGroups = [];

    protected $rootDir;


    public function __construct(array $groups)
    {
        $this->rootDir = Configuration::baseDir();
        $this->configuredGroups = $groups;
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
    protected function loadGroupsByPattern()
    {
        foreach ($this->configuredGroups as $group => $pattern) {
            if (strpos($group, '*') === false) {
                continue;
            }
            $path = dirname($pattern);
            if (!\Codeception\Util\PathResolver::isPathAbsolute($pattern)) {
                $path = $this->rootDir . $path;
            }

            $files = Finder::create()->files()
                ->name(basename($pattern))
                ->sortByName()
                ->in($path);

            $i = 1;


            foreach ($files as $file) {
                /** @var SplFileInfo $file * */
                $this->configuredGroups[str_replace('*', $i, $group)] = dirname($pattern).DIRECTORY_SEPARATOR.$file->getRelativePathname();
                $i++;
            }
            unset($this->configuredGroups[$group]);
        }
    }

    protected function loadConfiguredGroupSettings()
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

    /**
     * @param string $file
     * @param string $group
     * @return false|string
     * @throws ConfigurationException
     */
    private function normalizeFilePath($file, $group)
    {
        $pathParts = explode(':', $file);


        if (codecept_is_path_absolute($file)) {
            if ($file[0] === '/' && count($pathParts) > 1) {
                //take segment before first :
                $this->checkIfFileExists($pathParts[0], $group);
                return sprintf('%s:%s', realpath($pathParts[0]), $pathParts[1]);
            } else if (count($pathParts) > 2) {
                //on Windows take segment before second :
                $fullPath = $pathParts[0] . ':' . $pathParts[1];
                $this->checkIfFileExists($fullPath, $group);
                return sprintf('%s:%s', realpath($fullPath), $pathParts[2]);
            }

            $this->checkIfFileExists($file, $group);
            return realpath($file);
        } elseif (strpos($file, ':') === false) {
            $dirtyPath = $this->rootDir . $file;
            $this->checkIfFileExists($dirtyPath, $group);
            return realpath($dirtyPath);
        }

        $dirtyPath = $this->rootDir . $pathParts[0];
        $this->checkIfFileExists($dirtyPath, $group);
        return sprintf('%s:%s', realpath($dirtyPath), $pathParts[1]);
    }

    /**
     * @param string $path
     * @param string $group
     * @throws ConfigurationException
     */
    private function checkIfFileExists($path, $group)
    {
        if (!file_exists($path)) {
            throw new ConfigurationException('GroupManager: File or directory ' . $path . ' set in ' . $group. ' group does not exist');
        }
    }

    public function groupsForTest(\PHPUnit\Framework\Test $test)
    {
        $groups = [];
        $filename = Descriptor::getTestFileName($test);
        if ($test instanceof TestInterface) {
            $groups = $test->getMetadata()->getGroups();
        }
        if ($test instanceof Reported) {
            $info = $test->getReportFields();
            if (isset($info['class'])) {
                $groups = array_merge($groups, \PHPUnit\Util\Test::getGroups($info['class'], $info['name']));
            }
            $filename = str_replace(['\\\\', '//', '/./'], ['\\', '/', '/'], $info['file']);
        }
        if ($test instanceof \PHPUnit\Framework\TestCase) {
            $groups = array_merge($groups, \PHPUnit\Util\Test::getGroups(get_class($test), $test->getName(false)));
        }

        foreach ($this->testsInGroups as $group => $tests) {
            foreach ($tests as $testPattern) {
                if ($filename == $testPattern) {
                    $groups[] = $group;
                }
                if (strpos($filename . ':' . $test->getName(false), $testPattern) === 0) {
                    $groups[] = $group;
                }
                if ($test instanceof Gherkin
                    && mb_strtolower($filename . ':' . $test->getMetadata()->getFeature()) === mb_strtolower($testPattern)) {
                    $groups[] = $group;
                }
            }
        }
        return array_unique($groups);
    }
}
