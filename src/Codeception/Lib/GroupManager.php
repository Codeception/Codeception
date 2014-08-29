<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\Exception\Configuration as ConfigurationException;
use Codeception\TestCase\Interfaces\Descriptive;
use Codeception\TestCase\Interfaces\Reported;
use Codeception\TestCase\Interfaces\ScenarioDriven;
use Codeception\TestCase\Interfaces\Plain;
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
                ->path(dirname($pattern))
                ->sortByName()
                ->in(Configuration::projectDir());

            $i = 1;
            foreach ($files as $file) {
                /** @var SplFileInfo $file  **/
                $this->configuredGroups[str_replace('*', $i, $group)] = $file->getRelativePathname();
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
                    $this->testsInGroups[$group][] = Configuration::projectDir().$file;
                }
            } elseif (is_file(Configuration::projectDir().$tests)) {
                $handle = @fopen(Configuration::projectDir().$tests, "r");
                if ($handle) {
                    while (($test = fgets($handle, 4096)) !== false) {
                        $file = trim(Configuration::projectDir().$test);
                        $file = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $file);
                        $this->testsInGroups[$group][] = $file;
                    }
                    fclose($handle);
                }
            } else {
                codecept_debug("Group '$group' is empty, no tests are loaded");
            }
        }
    }

    public function groupsForTest(\PHPUnit_Framework_Test $test)
    {
        $groups = [];
        if ($test instanceof ScenarioDriven) {
            $groups = $test->getScenario()->getGroups();
        }
        if ($test instanceof Reported) {
            $info = $test->getReportFields();
            if (isset($info['class'])) {
                $groups = array_merge($groups, \PHPUnit_Util_Test::getGroups($info['class'], $info['name']));
            }
            $filename = $info['file'];
        } else {
            $groups = array_merge($groups, \PHPUnit_Util_Test::getGroups(get_class($test), $test->getName(false)));
            $filename = (new \ReflectionClass($test))->getFileName();
        }

        foreach ($this->testsInGroups as $group => $tests) {
            foreach ($tests as $testPattern) {
                if ($filename == $testPattern) {
                    $groups[] = $group;
                }

                if (strpos($filename . ':' . $test->getName(false), $testPattern) === 0) {
                    $groups[] = $group;
                }
            }
        }
        return array_unique($groups);
    }
}