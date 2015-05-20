<?php

use Codeception\Util\Stub;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateScenarioTest extends BaseCommandRunner {

    protected function setUp()
    {
        $this->modules = \Codeception\SuiteManager::$modules;
        $this->actions = \Codeception\SuiteManager::$actions;

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

    protected function tearDown()
    {
        \Codeception\SuiteManager::$modules = $this->modules;
        \Codeception\SuiteManager::$actions = $this->actions;
    }

    public function testBasic()
    {
        $this->execute(array('suite' => 'dummy'));
        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/data/scenarios/dummy/File_Exists.txt', $this->filename);
        $this->assertContains('I WANT TO CHECK CONFIG EXISTS', $this->content);
        $this->assertContains('I see file found "$codeception"', $this->content);
        $this->assertContains('* File_Exists generated', $this->output);
    }

    public function testHtml()
    {
        $this->execute(array('suite' => 'dummy', '--format' => 'html'));
        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/data/scenarios/dummy/File_Exists.html', $this->filename);
        $this->assertContains('<html><body><h3>I WANT TO CHECK CONFIG EXISTS</h3>', $this->content);
        $this->assertContains('I see file found "$codeception"', strip_tags($this->content));
        $this->assertContains('* File_Exists generated', $this->output);        
    }

    public function testOneFile()
    {
        $this->config['path'] = 'tests/data/claypit/tests/skipped/';
        $this->config['class_name'] = 'SkipGuy';

        $this->execute(array('suite' => 'skipped', '--single-file' => true));
        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/data/scenarios/skipped.txt', $this->filename);
        $this->assertContains('I WANT TO SKIP IT', $this->content);
        $this->assertContains('I WANT TO MAKE IT INCOMPLETE', $this->content);
        $this->assertContains('* Skip_Me rendered', $this->output);
        $this->assertContains('* Incomplete_Me rendered', $this->output);
    }

    public function testOneFileWithHtml()
    {
        $this->config['path'] = 'tests/data/claypit/tests/skipped/';
        $this->config['class_name'] = 'SkipGuy';

        $this->execute(array('suite' => 'skipped', '--single-file' => true, '--format' => 'html'));
        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/data/scenarios/skipped.html', $this->filename);
        $this->assertContains('<h3>I WANT TO MAKE IT INCOMPLETE</h3>', $this->content);
        $this->assertContains('<h3>I WANT TO SKIP IT</h3>', $this->content);
        $this->assertContains('<body><h3>', $this->content);
        $this->assertContains('</body></html>', $this->content);
        $this->assertContains('* Skip_Me rendered', $this->output);
        $this->assertContains('* Incomplete_Me rendered', $this->output);        
    }

    public function testDifferentPath()
    {
        $this->execute(array('suite' => 'dummy', '--single-file' => true, '--path' => 'docs'));
        $this->assertEquals('docs/dummy.txt', $this->filename);
        $this->assertContains('I WANT TO CHECK CONFIG EXISTS', $this->content);
        $this->assertContains('* File_Exists rendered', $this->output);

    }


}
