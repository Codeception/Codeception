<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\TestCase\Interfaces\Descriptive;
use Codeception\TestCase\Interfaces\Reported;
use Codeception\TestCase\Interfaces\ScenarioDriven;
use Codeception\TestCase\Interfaces\Plain;

/**
 * Loads information for groups from external sources (config, filesystem)
 *
 */
class GroupManager
{
    protected $configuredGroups;
    protected $testsInGroups = [];

    public function __construct(array $groups)
    {
        $this->configuredGroups = $groups;
        $this->loadConfiguredGroupSettings();
    }

    protected function loadConfiguredGroupSettings()
    {
        foreach ($this->configuredGroups as $group => $tests) {
            $this->testsInGroups[$group] = [];
            if (is_array($tests)) {
                foreach ($tests as $test) {
                    $this->testsInGroups[$group][] = Configuration::projectDir().$test;
                }
            } elseif (is_file(Configuration::projectDir().$tests)) {
                $handle = @fopen(Configuration::projectDir().$tests, "r");
                if ($handle) {
                    while (($test = fgets($handle, 4096)) !== false) {
                        $this->testsInGroups[$group][] = trim(Configuration::projectDir().$test);
                    }
                    fclose($handle);
                }
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