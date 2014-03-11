<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\TestCase\Interfaces\Reported;
use Codeception\Util\Stub;

class GroupManagerTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Codeception\Lib\GroupManager
     */
    protected $manager;

    // tests
    public function testGroupsFromArray()
    {
        $this->manager = new GroupManager(['important' => ['UserTest.php:testName', 'PostTest.php']]);
        $test1 = $this->makeTestCase('UserTest.php', 'testName');
        $test2 = $this->makeTestCase('PostTest.php');
        $test3 = $this->makeTestCase('UserTest.php', 'testNot');
        $this->assertContains('important', $this->manager->groupsForTest($test1));
        $this->assertContains('important', $this->manager->groupsForTest($test2));
        $this->assertNotContains('important', $this->manager->groupsForTest($test3));
    }

    public function testGroupsFromFile()
    {
        $this->manager = new GroupManager(['important' => 'tests/data/test_groups']);
        $test1 = $this->makeTestCase('tests/UserTest.php', 'testName');
        $test2 = $this->makeTestCase('tests/PostTest.php');
        $test3 = $this->makeTestCase('tests/UserTest.php', 'testNot');
        $this->assertContains('important', $this->manager->groupsForTest($test1));
        $this->assertContains('important', $this->manager->groupsForTest($test2));
        $this->assertNotContains('important', $this->manager->groupsForTest($test3));
    }

    protected function makeTestCase($file, $name = '')
    {
        return Stub::make('\Codeception\Lib\DescriptiveTestCase', [
                'getReportFields' => ['file' => codecept_root_dir() . $file],
                'getName' => $name]
        );
    }

}

class DescriptiveTestCase extends \Codeception\TestCase implements Reported
{
    public function getReportFields() {}
}