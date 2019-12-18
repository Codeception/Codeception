<?php
namespace Codeception\Lib;

use Codeception\Exception\ConfigurationException;
use Codeception\Util\Stub;
use Codeception\Test\Loader\Gherkin as GherkinLoader;

class GroupManagerTest extends \Codeception\Test\Unit
{
    /**
     * @var \Codeception\Lib\GroupManager
     */
    protected $manager;

    // tests
    public function testGroupsFromArray()
    {
        $this->manager = new GroupManager(['important' => ['tests/data/group_manager_test/UserTest.php:testName', 'tests/data/group_manager_test/PostTest.php']]);
        $test1 = $this->makeTestCase('tests/data/group_manager_test/UserTest.php', 'testName');
        $test2 = $this->makeTestCase('tests/data/group_manager_test/PostTest.php');
        $test3 = $this->makeTestCase('UserTest.php', 'testNot');
        $this->assertContains('important', $this->manager->groupsForTest($test1));
        $this->assertContains('important', $this->manager->groupsForTest($test2));
        $this->assertNotContains('important', $this->manager->groupsForTest($test3));
    }

    public function testRealPathForFileWithMethodName()
    {
        $this->manager = new GroupManager(['important' => ['tests/data/group_manager_test/PostTest.php:testName']]);
        $test = $this->makeTestCase('tests/data/group_manager_test/PostTest.php', 'testName');
        $this->assertContains('important', $this->manager->groupsForTest($test));
    }

    public function testGroupsFromFile()
    {
        $this->manager = new GroupManager(['important' => 'tests/data/group_manager_test/test_groups']);
        $test1 = $this->makeTestCase('tests/data/group_manager_test//UserTest.php', 'testName');
        $test2 = $this->makeTestCase('tests/data/group_manager_test//PostTest.php');
        $test3 = $this->makeTestCase('tests/data/group_manager_test//UserTest.php', 'testNot');
        $this->assertContains('important', $this->manager->groupsForTest($test1));
        $this->assertContains('important', $this->manager->groupsForTest($test2));
        $this->assertNotContains('important', $this->manager->groupsForTest($test3));
    }

    public function testGroupWithRelativePathsFromFile()
    {
        $this->manager = new GroupManager(['important' => 'tests/data/group_manager_test/relative_paths']);
        $test1 = $this->makeTestCase('tests/data/group_manager_test/UserTest.php', 'testName');
        $test2 = $this->makeTestCase('tests/data/group_manager_test/PostTest.php');
        $test3 = $this->makeTestCase('tests/data/group_manager_test/UserTest.php', 'testNot');
        $this->assertContains('important', $this->manager->groupsForTest($test1));
        $this->assertContains('important', $this->manager->groupsForTest($test2));
        $this->assertNotContains('important', $this->manager->groupsForTest($test3));
    }

    public function testGroupsFromFileOnWindows()
    {
        $this->manager = new GroupManager(['important' => 'tests//data/group_manager_test/group_3']);
        $test = $this->makeTestCase('tests/data/group_manager_test/WinTest.php');
        $this->assertContains('important', $this->manager->groupsForTest($test));
    }

    public function testGroupsFromArrayOnWindows()
    {
        $this->manager = new GroupManager(['important' => ['tests\data\group_manager_test\WinTest.php']]);
        $test = $this->makeTestCase('tests/data/group_manager_test/WinTest.php');
        $this->assertContains('important', $this->manager->groupsForTest($test));
    }

    public function testGroupsByPattern()
    {
        $this->manager = new GroupManager(['group_*' => 'tests/data/group_manager_test/group_*']);
        $test1 = $this->makeTestCase('tests/data/group_manager_test/UserTest.php');
        $test2 = $this->makeTestCase('tests/data/group_manager_test/PostTest.php');
        $this->assertContains('group_1', $this->manager->groupsForTest($test1));
        $this->assertContains('group_2', $this->manager->groupsForTest($test2));
    }

    public function testGroupsByDifferentPattern()
    {
        $this->manager = new GroupManager(['g_*' => 'tests/data/group_manager_test/group_*']);
        $test1 = $this->makeTestCase('tests/data/group_manager_test/UserTest.php');
        $test2 = $this->makeTestCase('tests/data/group_manager_test/PostTest.php');
        $this->assertContains('g_1', $this->manager->groupsForTest($test1));
        $this->assertContains('g_2', $this->manager->groupsForTest($test2));
    }

    public function testGroupsFileHandlesWhitespace()
    {
        $this->manager = new GroupManager(['whitespace_group_test' => 'tests/data/group_manager_test/whitespace_group_test']);
        $goodTest = $this->makeTestCase('tests/data/group_manager_test/UserTest.php');
        $badTest = $this->makeTestCase('');

        $this->assertContains('whitespace_group_test', $this->manager->groupsForTest($goodTest));
        $this->assertEmpty($this->manager->groupsForTest($badTest));
    }

    public function testLoadSpecificScenarioFromFile()
    {
        $this->manager = new GroupManager(['gherkinGroup1' => 'tests/data/group_manager_test/gherkinGroup1']);
        $loader = new GherkinLoader();
        $loader->loadTests(codecept_absolute_path('tests/data/refund.feature'));
        $test = $loader->getTests()[0];
        $this->assertContains('gherkinGroup1', $this->manager->groupsForTest($test));
    }

    public function testLoadSpecificScenarioWithMultibyteStringFromFile()
    {
        $this->manager = new GroupManager(['gherkinGroup2' => 'tests/data/group_manager_test/gherkinGroup2']);
        $loader = new GherkinLoader();
        $loader->loadTests(codecept_absolute_path('tests/data/refund2.feature'));
        $test = $loader->getTests()[0];
        $this->assertContains('gherkinGroup2', $this->manager->groupsForTest($test));
    }

    public function testThrowsExceptionIfDirectoryDoesNotExists()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('tests/data/missing-directory');
        $this->expectExceptionMessage('does not exist');
        new GroupManager(['invalidGroup' => ['tests/data/missing-directory']]);
    }

    public function testThrowsExceptionIfDirectoryDoesNotExistsWithColonAndTestName()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('tests/data/missing-directory');
        $this->expectExceptionMessage('does not exist');
        new GroupManager(['invalidGroup' => ['tests/data/missing-directory:testName']]);
    }

    public function testThrowsExceptionIfDirectoryInGroupFileDoesNotExists()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('tests/data/missing-directory');
        $this->expectExceptionMessage('does not exist');
        new GroupManager(['important' => 'tests/data/group_manager_test/missing_directory']);
    }


    protected function makeTestCase($file, $name = '')
    {
        return Stub::make(
            '\Codeception\Lib\DescriptiveTestCase',
            [
                'getReportFields' => ['file' => codecept_root_dir() . $file],
                'getName' => $name
            ]
        );
    }
}

class DescriptiveTestCase extends \Codeception\Test\Unit
{

}
