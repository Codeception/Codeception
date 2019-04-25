<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateCeptTest extends BaseCommandRunner
{

    protected function _setUp()
    {
        $this->makeCommand('\Codeception\Command\GenerateCept');
        $this->config = array(
            'actor' => 'HobbitGuy',
            'path' => 'tests/shire',
        );
    }

    public function testGenerateBasic()
    {
        $this->execute(array('suite' => 'shire', 'test' => 'HomeCanInclude12Dwarfs'));
        $this->assertEquals($this->filename, 'tests/shire/HomeCanInclude12DwarfsCept.php');
        $this->assertStringContainsString('$I = new HobbitGuy($scenario);', $this->content);
        $this->assertStringContainsString('Test was created in tests/shire/HomeCanInclude12DwarfsCept.php', $this->output);
        $this->assertIsValidPhp($this->content);
    }

    public function testGenerateWithSuffix()
    {
        $this->execute(array('suite' => 'shire', 'test' => 'HomeCanInclude12DwarfsCept'));
        $this->assertEquals($this->filename, 'tests/shire/HomeCanInclude12DwarfsCept.php');
        $this->assertIsValidPhp($this->content);
    }

    /**
     * @group command
     */
    public function testGenerateWithFullName()
    {
        $this->execute(array('suite' => 'shire', 'test' => 'HomeCanInclude12DwarfsCept.php'));
        $this->assertEquals($this->filename, 'tests/shire/HomeCanInclude12DwarfsCept.php');
        $this->assertIsValidPhp($this->content);
    }

    /**
     * @group command
     */
    public function testGenerateWithGuyNamespaced()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(array('suite' => 'shire', 'test' => 'HomeCanInclude12Dwarfs'));
        $this->assertEquals($this->filename, 'tests/shire/HomeCanInclude12DwarfsCept.php');
        $this->assertStringContainsString('use MiddleEarth\HobbitGuy;', $this->content);
        $this->assertStringContainsString('$I = new HobbitGuy($scenario);', $this->content);
        $this->assertIsValidPhp($this->content);
    }
}
