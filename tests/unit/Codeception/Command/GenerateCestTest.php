<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateCestTest extends BaseCommandRunner
{

    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\GenerateCest');
        $this->config = array(
            'class_name' => 'HobbitGuy',
            'path' => 'tests/shire',
        );
    }

    /**
     * @group command
     */
    public function testBasic()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'HallUnderTheHill'));
        $this->assertEquals('tests/shire/HallUnderTheHillCest.php', $this->filename);

        $this->assertContains('class HallUnderTheHillCest', $this->content);
        $this->assertContains('public function _before(HobbitGuy $I)', $this->content);
        $this->assertContains('public function _after(HobbitGuy $I)', $this->content);
        $this->assertContains('public function tryToTest(HobbitGuy $I)', $this->content);
        $this->assertContains('Test was created in tests/shire/HallUnderTheHillCest.php', $this->output);
    }

    /**
     * @group command
     */
    public function testNamespaced()
    {
        $this->config['namespace'] = 'Shire';
        $this->execute(array('suite' => 'shire', 'class' => 'HallUnderTheHill'));
        $this->assertContains('namespace Shire;', $this->content);
        $this->assertContains('use Shire\HobbitGuy;', $this->content);
        $this->assertContains('class HallUnderTheHillCest', $this->content);
    }

    /**
     * @group command
     */
    public function testGenerateWithFullName()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'HomeCanInclude12DwarfsCest.php'));
        $this->assertEquals('tests/shire/HomeCanInclude12DwarfsCest.php', $this->filename);
    }

    /**
     * @group command
     */
    public function testGenerateWithSuffix()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'HomeCanInclude12DwarfsCest'));
        $this->assertEquals($this->filename, 'tests/shire/HomeCanInclude12DwarfsCest.php');
        $this->assertIsValidPhp($this->content);
    }

    public function testGenerateWithGuyNamespaced()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(array('suite' => 'shire', 'class' => 'HallUnderTheHillCest'));
        $this->assertEquals($this->filename, 'tests/shire/HallUnderTheHillCest.php');
        $this->assertContains('namespace MiddleEarth;', $this->content);
        $this->assertContains('use MiddleEarth\\HobbitGuy;', $this->content);
        $this->assertContains('public function tryToTest(HobbitGuy $I)', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateWithNamespace()
    {
        $this->execute(array('suite' => 'shire', 'class' => 'MiddleEarth\HallUnderTheHillCest'));
        $this->assertEquals('tests/shire/MiddleEarth/HallUnderTheHillCest.php', $this->filename);
        $this->assertContains('namespace MiddleEarth;', $this->content);
        $this->assertContains('class HallUnderTheHillCest', $this->content);
        $this->assertContains('Test was created in tests/shire/MiddleEarth/HallUnderTheHillCest.php', $this->output);
    }
}
