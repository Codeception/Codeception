<?php
namespace Codeception\Lib;

use Codeception\Configuration;
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

    public function __construct(array $groups)
    {
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
            $files = Finder::create()->files()
                ->name(basename($pattern))
                ->sortByName()
                ->in(Configuration::projectDir().dirname($pattern));

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
                    $this->testsInGroups[$group][] = Configuration::projectDir() . $file;
                }
            } elseif (is_file(Configuration::projectDir() . $tests)) {
                $handle = @fopen(Configuration::projectDir() . $tests, "r");
                if ($handle) {
                    while (($test = fgets($handle, 4096)) !== false) {
                        // if the current line is blank then we need to move to the next line
                        // otherwise the current codeception directory becomes part of the group
                        // which causes every single test to run
                        if (trim($test) === '') {
                            continue;
                        }

                        $file = trim(Configuration::projectDir() . $test);
                        $file = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $file);
                        $this->testsInGroups[$group][] = $file;
                    }
                    fclose($handle);
                }
            }
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
            $filename = str_replace(['\\\\', '//'], ['\\', '/'], $info['file']);
        }
        if ($test instanceof \PHPUnit\Framework\TestCase) {
            $groups = array_merge($groups, \PHPUnit\Util\Test::getGroups(get_class($test), $test->getName(false)));
        }
        if ($test instanceof \PHPUnit\Framework\TestSuite\DataProvider) {
            $firstTest = $test->testAt(0);
            if ($firstTest != false && $firstTest instanceof TestInterface) {
                $groups = array_merge($groups, $firstTest->getMetadata()->getGroups());
                $filename = Descriptor::getTestFileName($firstTest);
            }
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
                if ($test instanceof \PHPUnit\Framework\TestSuite\DataProvider) {
                    $firstTest = $test->testAt(0);
                    if ($firstTest != false && $firstTest instanceof TestInterface) {
                        if (strpos($filename . ':' . $firstTest->getName(false), $testPattern) === 0) {
                            $groups[] = $group;
                        }
                    }
                }
            }
        }
        return array_unique($groups);
    }
}
