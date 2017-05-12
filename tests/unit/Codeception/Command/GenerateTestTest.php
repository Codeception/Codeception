<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateTestTest extends BaseCommandRunner
{

    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\GenerateTest');
        $this->config = array(
            'actor' => 'HobbitGuy',
            'path' => 'tests/shire',
        );
    }

    public function testBasic()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'HallUnderTheHill'));
        $this->assertEquals('tests/shire/HallUnderTheHillTest.php', $this->filename);
        $this->assertContains('class HallUnderTheHillTest extends \Codeception\Test\Unit', $this->content);
        $this->assertContains('Test was created in tests/shire/HallUnderTheHillTest.php', $this->output);
        $this->assertContains('protected function _before()', $this->content);
        $this->assertContains('protected function _after()', $this->content);
    }

    public function testCreateWithSuffix()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'HallUnderTheHillTest'));
        $this->assertEquals('tests/shire/HallUnderTheHillTest.php', $this->filename);
        $this->assertContains('Test was created in tests/shire/HallUnderTheHillTest.php', $this->output);
    }

    public function testCreateWithNamespace()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'MiddleEarth\HallUnderTheHillTest'));
        $this->assertEquals('tests/shire/MiddleEarth/HallUnderTheHillTest.php', $this->filename);
        $this->assertContains('namespace MiddleEarth;', $this->content);
        $this->assertContains('class HallUnderTheHillTest extends \Codeception\Test\Unit', $this->content);
        $this->assertContains('Test was created in tests/shire/MiddleEarth/HallUnderTheHillTest.php', $this->output);
    }

    public function testCreateWithExtension()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'HallUnderTheHillTest.php'));
        $this->assertEquals('tests/shire/HallUnderTheHillTest.php', $this->filename);
        $this->assertContains('class HallUnderTheHillTest extends \Codeception\Test\Unit', $this->content);
        $this->assertContains('protected $tester;', $this->content);
        $this->assertContains('@var \HobbitGuy', $this->content);
        $this->assertContains('Test was created in tests/shire/HallUnderTheHillTest.php', $this->output);
    }

    public function testValidPHP()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'HallUnderTheHill'));
        $this->assertIsValidPhp($this->content);
    }
}
