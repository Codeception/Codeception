<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GeneratePageObjectTest extends BaseCommandRunner
{

    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\GeneratePageObject');
        $this->config = array(
            'actor' => 'HobbitGuy',
            'path' => 'tests/shire',
            'paths' => array('tests' => 'tests'),
            'settings' => array('bootstrap' => '_bootstrap.php')
        );
    }

    public function testBasic()
    {
        unset($this->config['actor']);
        $this->execute(array('suite' => 'Login'), false);
        $this->assertEquals(\Codeception\Configuration::supportDir().'Page/Login.php', $this->filename);
        $this->assertContains('class Login', $this->content);
        $this->assertContains('public static', $this->content);
        $this->assertNotContains('public function __construct', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testNamespace()
    {
        unset($this->config['actor']);
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(array('suite' => 'Login'), false);
        $this->assertEquals(\Codeception\Configuration::supportDir().'Page/Login.php', $this->filename);
        $this->assertContains('namespace MiddleEarth\Page;', $this->content);
        $this->assertContains('class Login', $this->content);
        $this->assertContains('public static', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateForSuite()
    {
        $this->execute(array('suite' => 'shire', 'page' => 'Login'));
        $this->assertEquals(\Codeception\Configuration::supportDir().'Page/Shire/Login.php', $this->filename);
        $this->assertContains('namespace Page\Shire;', $this->content);
        $this->assertContains('class Login', $this->content);
        $this->assertContains('protected $hobbitGuy;', $this->content);
        $this->assertContains('public function __construct(\HobbitGuy $I)', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateForSuiteWithNamespace()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(array('suite' => 'shire', 'page' => 'Login'));
        $this->assertEquals(\Codeception\Configuration::supportDir().'Page/Shire/Login.php', $this->filename);
        $this->assertContains('namespace MiddleEarth\Page\Shire;', $this->content);
        $this->assertContains('class Login', $this->content);
        $this->assertContains('protected $hobbitGuy;', $this->content);
        $this->assertContains('public function __construct(\MiddleEarth\HobbitGuy $I)', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateInSubpath()
    {
        $this->execute(array('suite' => 'User/View'));
        $this->assertEquals(\Codeception\Configuration::supportDir().'Page/User/View.php', $this->filename);
        $this->assertIsValidPhp($this->content);
    }
}
