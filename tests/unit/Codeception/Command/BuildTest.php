<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class BuildTest extends BaseCommandRunner
{

    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\Build');
        $this->config = array(
            'class_name' => 'HobbitGuy',
            'path' => 'tests/shire/',
            'modules' => array('enabled' => array('Filesystem', 'EmulateModuleHelper')),
            'include' => []
        );
    }

    public function testBuild()
    {
        $this->execute();
        $this->assertContains('class HobbitGuy extends \Codeception\Actor', $this->content);

        // methods from Filesystem module
        $this->assertContains('public function amInPath($path)', $this->content);
        $this->assertContains('public function copyDir($src, $dst)', $this->content);
        $this->assertContains('public function seeInThisFile($text)', $this->content);

        // methods from EmulateHelper
        $this->assertContains('public function seeEquals($expected, $actual)', $this->content);

        // inherited methods from AbstractGuy
        $this->assertContains('@method void wantTo($text)', $this->content);
        $this->assertContains('@method void expectTo($prediction)', $this->content);

        $this->assertContains('HobbitGuy.php generated successfully.', $this->output);
        $this->assertIsValidPhp($this->content);
    }

    public function testBuildNamespacedGuy()
    {
        $this->config['namespace'] = 'Shire';
        $this->execute();
        $this->assertContains('namespace Shire;', $this->content);
        $this->assertContains('class HobbitGuy extends \Codeception\Actor', $this->content);
        $this->assertContains('public function amInPath($path)', $this->content);
        $this->assertIsValidPhp($this->content);
    }

}
