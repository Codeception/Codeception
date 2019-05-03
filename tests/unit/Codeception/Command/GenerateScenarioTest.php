<?php

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Stub;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateScenarioTest extends BaseCommandRunner
{

    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;

    protected function _setUp()
    {
        $this->moduleContainer = new ModuleContainer(Stub::make('Codeception\Lib\Di'), []);
        $this->moduleContainer->create('EmulateModuleHelper');

        $this->modules = $this->moduleContainer->all();
        $this->actions = $this->moduleContainer->getActions();
        $this->filename = null;

        $this->makeCommand('\Codeception\Command\GenerateScenarios');
        $this->config = array(
            'paths' => array(
                'tests' => 'tests/data/claypit/tests/',
                'data' => '_data',

            ),
            'class_name' => 'DumbGuy',
            'path' => 'tests/data/claypit/tests/dummy/'
        );
    }

    public function testBasic()
    {
        $this->execute(array('suite' => 'dummy'));
        $file = codecept_root_dir().'tests/data/scenarios/dummy/File_Exists.txt';
        $this->assertArrayHasKey($file, $this->saved);
        $content = $this->saved[$file];
        $this->assertStringContainsString('I WANT TO CHECK CONFIG EXISTS', $content);
        $this->assertStringContainsString('I see file found "$codeception"', $content);
        $this->assertStringContainsString('* File_Exists generated', $this->output);
    }

    public function testMultipleTestsGeneration()
    {
        $this->execute(['suite' => 'dummy']);
        $this->assertArrayHasKey(codecept_root_dir().'tests/data/scenarios/dummy/Another.optimistic.txt', $this->saved);
        $this->assertArrayHasKey(codecept_root_dir().'tests/data/scenarios/dummy/Another.pessimistic.txt', $this->saved);
        $file = codecept_root_dir().'tests/data/scenarios/dummy/File_Exists.txt';
        $this->assertArrayHasKey($file, $this->saved);
        $content = $this->saved[$file];
        $this->assertStringContainsString('I WANT TO CHECK CONFIG EXISTS', $content);
        $this->assertStringContainsString('I see file found "$codeception"', $content);
        $this->assertStringContainsString('* File_Exists generated', $this->output);
    }

    public function testHtml()
    {
        $this->execute(array('suite' => 'dummy', '--format' => 'html'));
        $file = codecept_root_dir().'tests/data/scenarios/dummy/File_Exists.html';
        $this->assertArrayHasKey($file, $this->saved);
        $content = $this->saved[$file];
        $this->assertStringContainsString('<html><body><h3>I WANT TO CHECK CONFIG EXISTS</h3>', $content);
        $this->assertStringContainsString('I see file found &quot;$codeception&quot;', strip_tags($content));
        $this->assertStringContainsString('* File_Exists generated', $this->output);
    }

    public function testOneFile()
    {
        $this->config['path'] = 'tests/data/claypit/tests/skipped/';
        $this->config['class_name'] = 'SkipGuy';

        $this->execute(array('suite' => 'skipped', '--single-file' => true));
        $this->assertEquals(codecept_root_dir().'tests/data/scenarios/skipped.txt', $this->filename);
        $this->assertStringContainsString('I WANT TO SKIP IT', $this->content);
        $this->assertStringContainsString('I WANT TO MAKE IT INCOMPLETE', $this->content);
        $this->assertStringContainsString('* Skip_Me rendered', $this->output);
        $this->assertStringContainsString('* Incomplete_Me rendered', $this->output);
    }

    public function testOneFileWithHtml()
    {
        $this->config['path'] = 'tests/data/claypit/tests/skipped/';
        $this->config['class_name'] = 'SkipGuy';

        $this->execute(array('suite' => 'skipped', '--single-file' => true, '--format' => 'html'));
        $this->assertEquals(codecept_root_dir().'tests/data/scenarios/skipped.html', $this->filename);
        $this->assertStringContainsString('<h3>I WANT TO MAKE IT INCOMPLETE</h3>', $this->content);
        $this->assertStringContainsString('<h3>I WANT TO SKIP IT</h3>', $this->content);
        $this->assertStringContainsString('<body><h3>', $this->content);
        $this->assertStringContainsString('</body></html>', $this->content);
        $this->assertStringContainsString('* Skip_Me rendered', $this->output);
        $this->assertStringContainsString('* Incomplete_Me rendered', $this->output);
    }

    public function testDifferentPath()
    {
        $this->execute(array('suite' => 'dummy', '--single-file' => true, '--path' => 'docs'));
        $this->assertEquals('docs/dummy.txt', $this->filename);
        $this->assertStringContainsString('I WANT TO CHECK CONFIG EXISTS', $this->content);
        $this->assertStringContainsString('* File_Exists rendered', $this->output);
    }
}
