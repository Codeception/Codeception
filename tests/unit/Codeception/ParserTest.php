<?php
use Codeception\Lib\Parser;
use \Codeception\Util\Stub;

class ParserTest extends \Codeception\TestCase\Test
{
    /**
     * @var Parser
     */
    protected $parser;
    /**
     * @var \CodeGuy
     */
    protected $codeGuy;

    protected function _before()
    {
        $this->scenario = new \Codeception\Scenario(Stub::make('Codeception\TestCase\Cept'));
        $this->parser = new Parser($this->scenario);
    }

    public function testParsingFeature()
    {
        $code = "<?php\n \\\$I->wantTo('run this test'); ";
        $this->parser->parseFeature($code);
        $this->assertEquals('run this test', $this->scenario->getFeature());

        $code = "<?php\n \\\$I->wantToTest('this run'); ";
        $this->parser->parseFeature($code);
        $this->assertEquals('test this run', $this->scenario->getFeature());
    }

    public function testParsingWithWhitespace()
    {
        $code = "<?php\n \\\$I->wantTo( 'run this test' ); ";
        $this->parser->parseFeature($code);
        $this->assertEquals('run this test', $this->scenario->getFeature());
    }

    public function testScenarioOptions()
    {
        $code = "<?php\n \$scenario->group('davert'); \$scenario->env('windows');";
        $this->parser->parseScenarioOptions($code);
        $this->assertContains('davert', $this->scenario->getGroups());
        $this->assertContains('windows', $this->scenario->getEnv());

    }

    public function testCommentedScenarioOptions()
    {
        $code = "<?php\n// \$scenario->skip();";
        $this->parser->parseScenarioOptions($code);
        $this->assertFalse($this->scenario->isBlocked());
    }

    public function testCommentedInBlockScenarioOptions()
    {
        $code = "<?php\n/*
         \$scenario->skip();
         */";
        $this->parser->parseScenarioOptions($code);
        $this->assertFalse($this->scenario->isBlocked());
    }

    public function testScenarioOptionsWithParam()
    {
        $code = "<?php\n
         \$alalala->skip();
         ";
        $this->parser->parseScenarioOptions($code, 'alalala');
        $this->assertTrue($this->scenario->isBlocked());
    }

    public function testScenarioOptionsIgnoredWhenNull()
    {
        $code = "<?php\n
         \$scenario->skip();
         ";
        $this->parser->parseScenarioOptions($code, null);
        $this->assertFalse($this->scenario->isBlocked());
    }

    public function testFeatureCommented()
    {
        $code = "<?php\n //\\\$I->wantTo('run this test'); ";
        $this->parser->parseFeature($code);
        $this->assertNull($this->scenario->getFeature());

        $code = "<?php\n /*\n \\\$I->wantTo('run this test'); \n */";
        $this->parser->parseFeature($code);
        $this->assertNull($this->scenario->getFeature());

    }


    public function testScenarioSkipOptionsHandled()
    {
        $this->setExpectedException('PHPUnit_Framework_SkippedTestError', 'pass along');
        $code = "<?php\n \$scenario->skip('pass along'); ";
        $this->parser->parseScenarioOptions($code);
        $this->assertTrue($this->scenario->isBlocked());
        $this->scenario->run();
    }

    public function testScenarioIncompleteOptionHandled()
    {
        $this->setExpectedException('PHPUnit_Framework_IncompleteTestError', 'not ready yet');
        $code = "<?php\n \$scenario->incomplete('not ready yet'); ";
        $this->parser->parseScenarioOptions($code);
        $this->assertTrue($this->scenario->isBlocked());
        $this->scenario->run();
    }

    public function testSteps()
    {
        $code = file_get_contents(\Codeception\Configuration::projectDir().'tests/cli/UnitCept.php');
        $this->assertContains('$I->seeInThisFile', $code);
        $this->parser->parseSteps($code);
        $text = $this->scenario->getText();
        $this->assertContains("I see in this file", $text);
    }

    public function testStepsWithFriends()
    {
        $code = file_get_contents(\Codeception\Configuration::projectDir().'tests/web/FriendsCept.php');
        $this->assertContains('$I->haveFriend', $code);
        $this->parser->parseSteps($code);
        $text = $this->scenario->getText();
        $this->assertContains("jon does", $text);
        $this->assertContains("I have friend", $text);
        $this->assertContains("back to me", $text);

    }

    public function testParseFile()
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('SimpleTest.php'));
        $this->assertEquals(['SampleTest'], $classes);
    }

    public function testParseFileWithClass()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $this->markTestSkipped('only for php 5.5');
        }
        $classes = Parser::getClassesFromFile(codecept_data_dir('php55Test'));
        $this->assertEquals(['php55Test'], $classes);
    }
    
    /*
     * https://github.com/Codeception/Codeception/issues/1779
     */
    public function testParseFileWhichUnsetsFileVariable()
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('unsetFile.php'));
        $this->assertEquals([], $classes);
    }
}