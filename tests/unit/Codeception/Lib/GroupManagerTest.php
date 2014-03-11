<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\TestCase\Interfaces\Descriptive;
use Codeception\TestCase\Interfaces\Plain;
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
        $dir = Configuration::projectDir();
        $this->manager = new GroupManager(['important' => ['UserTest.php:testName', 'PostTest.php']]);
        $test1 = Stub::make('\Codeception\Lib\DescriptiveTestCase', ['getFileName' => $dir . 'UserTest.php', 'getName' => 'testName']);
        $test2 = Stub::make('\Codeception\Lib\DescriptiveTestCase', ['getFileName' => $dir . 'PostTest.php']);
        $test3 = Stub::make('\Codeception\Lib\DescriptiveTestCase', ['getFileName' => $dir . 'UserTest.php', 'getName' => 'testNot']);
        $this->assertContains('important', $this->manager->groupsForTest($test1));
        $this->assertContains('important', $this->manager->groupsForTest($test2));
        $this->assertNotContains('important', $this->manager->groupsForTest($test3));
    }

    public function testGroupsFromFile()
    {
        $dir = Configuration::projectDir();
        $this->manager = new GroupManager(['important' => 'tests/data/test_groups']);
        $test1 = Stub::make('\Codeception\Lib\DescriptiveTestCase', ['getFileName' => $dir . 'tests/UserTest.php', 'getName' => 'testName']);
        $test2 = Stub::make('\Codeception\Lib\DescriptiveTestCase', ['getFileName' => $dir . 'tests/PostTest.php']);
        $test3 = Stub::make('\Codeception\Lib\DescriptiveTestCase', ['getFileName' => $dir . 'tests/UserTest.php', 'getName' => 'testNot']);
        $this->assertContains('important', $this->manager->groupsForTest($test1));
        $this->assertContains('important', $this->manager->groupsForTest($test2));
        $this->assertNotContains('important', $this->manager->groupsForTest($test3));
    }

}

class DescriptiveTestCase extends \Codeception\TestCase implements Descriptive, Plain
{
    public function getFileName() {}
    public function getSignature() {}
}