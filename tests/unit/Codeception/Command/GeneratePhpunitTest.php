<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GeneratePhpunitTest extends BaseCommandRunner {

    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\GeneratePhpUnit');
        $this->config = array(
            'class_name' => 'HobbitGuy',
            'path' => 'tests/shire',
        );
    }

    public function testBasic()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'Pony'));
        $this->assertEquals('tests/shire/PonyTest.php', $this->filename);
        $this->assertContains('class PonyTest extends \PHPUnit_Framework_TestCase', $this->content);
        $this->assertContains('Test was created in tests/shire/PonyTest.php', $this->output);
        $this->assertContains('protected function setUp()', $this->content);
        $this->assertContains('protected function tearDown()', $this->content);
    }

    public function testCreateWithSuffix()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'PonyTest'));
        $this->assertEquals('tests/shire/PonyTest.php', $this->filename);
        $this->assertContains('class PonyTest extends \PHPUnit_Framework_TestCase', $this->content);
        $this->assertContains('Test was created in tests/shire/PonyTest.php', $this->output);
    }

    public function testCreateWithExtension()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'PonyTest.php'));
        $this->assertEquals('tests/shire/PonyTest.php', $this->filename);
        $this->assertContains('class PonyTest extends \PHPUnit_Framework_TestCase', $this->content);
        $this->assertContains('Test was created in tests/shire/PonyTest.php', $this->output);
    }

    public function testCreateWithNamespace()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'MiddleEarth\Pony'));
        $this->assertEquals('tests/shire/PonyTest.php', $this->filename);
        $this->assertContains('namespace MiddleEarth;', $this->content);
        $this->assertContains('class PonyTest extends \PHPUnit_Framework_TestCase', $this->content);
        $this->assertContains('Test was created in tests/shire/PonyTest.php', $this->output);
    }

    public function testValidPHP()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'Pony'));
        $this->assertTrue($this->isValidPhp());
    }


}
