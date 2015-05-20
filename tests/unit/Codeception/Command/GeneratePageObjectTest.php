<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GeneratePageObjectTest extends BaseCommandRunner
{

    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\GeneratePageObject');
        $this->config = array(
            'class_name' => 'HobbitGuy',
            'path' => 'tests/shire',
            'paths' => array('tests' => 'tests'),
            'settings' => array('bootstrap' => '_bootstrap.php')
        );
    }

    public function testBasic()
    {
        unset($this->config['class_name']);
        $this->execute(array('page' => 'Login'), false);
        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/_pages/LoginPage.php', $this->filename);
        $this->assertContains('class LoginPage', $this->content);
        $this->assertContains('public static', $this->content);
        $this->assertNotContains('public function __construct', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testNamespace()
    {
        unset($this->config['class_name']);
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(array('page' => 'Login'), false);
        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/_pages/LoginPage.php', $this->filename);
        $this->assertContains('namespace MiddleEarth;', $this->content);
        $this->assertContains('class LoginPage', $this->content);
        $this->assertContains('public static', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateForSuite()
    {
        $this->execute(array('suite' => 'shire','page' => 'Login'));
        $this->assertEquals('tests/shire/_pages/LoginPage.php', $this->filename);
        $this->assertContains('class LoginPage', $this->content);
        $this->assertContains('protected $hobbitGuy;', $this->content);
        $this->assertContains('public function __construct(HobbitGuy $I)', $this->content);
        $this->assertContains('public static function of(HobbitGuy $I)', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateForSuiteWithNamespace()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(array('suite' => 'shire','page' => 'Login'));
        $this->assertEquals('tests/shire/_pages/LoginPage.php', $this->filename);
        $this->assertContains('namespace MiddleEarth;', $this->content);
        $this->assertContains('class LoginPage', $this->content);
        $this->assertContains('protected $hobbitGuy;', $this->content);
        $this->assertContains('public function __construct(HobbitGuy $I)', $this->content);
        $this->assertContains('public static function of(HobbitGuy $I)', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateInSubpath()
    {
        $this->execute(array('page' => 'user/viewPage'));
        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/_pages/viewPage.php', $this->filename);
        $this->assertIsValidPhp($this->content);
    }
}