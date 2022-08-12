<?php

declare(strict_types=1);

class BuildTest extends BaseCommandRunner
{
    /**
     * @var array
     */
    public array $log = [];

    protected function _setUp()
    {
        $this->makeCommand(\Codeception\Command\Build::class);
        $this->config = [
            'actor' => 'HobbitGuy',
            'path' => 'tests/shire/',
            'modules' => ['enabled' => ['Filesystem', 'EmulateModuleHelper']],
            'include' => []
        ];
    }

    public function testBuild()
    {
        $this->execute();
        $this->assertStringContainsString('class HobbitGuy extends \Codeception\Actor', $this->content);
        // inherited methods from Actor
        $this->assertStringContainsString('@method void wantTo($text)', $this->content);
        $this->assertStringContainsString('@method void expectTo($prediction)', $this->content);

        $this->content = $this->log[0]['content'];
        // methods from Filesystem module
        $this->assertStringContainsString('public function amInPath(string $path): void', $this->content);
        $this->assertStringContainsString('public function copyDir(string $src, string $dst): void', $this->content);
        $this->assertStringContainsString('public function seeInThisFile(string $text): void', $this->content);

        // methods from EmulateHelper
        $this->assertStringContainsString('public function seeEquals($expected, $actual)', $this->content);

        $this->assertStringContainsString('HobbitGuyActions.php generated successfully.', $this->output);
        $this->assertIsValidPhp($this->content);
    }

    public function testBuildNamespacedActor()
    {
        $this->config['namespace'] = 'Shire';
        $this->execute();
        $this->assertStringContainsString('namespace Shire;', $this->content);
        $this->assertStringContainsString('class HobbitGuy extends \Codeception\Actor', $this->content);
        $this->assertStringContainsString('use _generated\HobbitGuyActions;', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testBuildNamespacedActorInSupportNamespace()
    {
        $this->config['namespace'] = 'Shire';
        $this->config['support_namespace'] = 'Support';
        $this->execute();
        $this->assertStringContainsString('namespace Shire\Support;', $this->content);
        $this->assertStringContainsString('class HobbitGuy extends \Codeception\Actor', $this->content);
        $this->assertStringContainsString('use _generated\HobbitGuyActions;', $this->content);
        $this->assertIsValidPhp($this->content);
    }
}
