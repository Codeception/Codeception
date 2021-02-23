<?php

declare(strict_types=1);

use Codeception\Lib\Parser;

/**
 * @group core
 * Class ParserTest
 */
class ParserTest extends \Codeception\Test\Unit
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var \Codeception\Scenario
     */
    protected $scenario;

    /**
     * @var \Codeception\Test\Metadata
     */
    protected $testMetadata;

    protected function _before(): void
    {
        $cept = new \Codeception\Test\Cept('demo', 'DemoCept.php');

        $this->testMetadata = $cept->getMetadata();
        $this->scenario = new Codeception\Scenario($cept);
        $this->parser = new Parser($this->scenario, $this->testMetadata);
    }

    public function testParsingFeature(): void
    {
        $code = "<?php\n \\\$I->wantTo('run this test'); ";
        $this->parser->parseFeature($code);
        $this->assertEquals('run this test', $this->scenario->getFeature());

        $code = "<?php\n \\\$I->wantToTest('this run'); ";
        $this->parser->parseFeature($code);
        $this->assertEquals('test this run', $this->scenario->getFeature());
    }

    public function testParsingWithWhitespace(): void
    {
        $code = "<?php\n \\\$I->wantTo( 'run this test' ); ";
        $this->parser->parseFeature($code);
        $this->assertEquals('run this test', $this->scenario->getFeature());
    }

    public function testScenarioOptions(): void
    {
        $code = <<<EOF
<?php
// @group davert
// @env windows

\$I = new AcceptanceTeser(\$scenario);
EOF;

        $this->parser->parseScenarioOptions($code);
        $this->assertContains('davert', $this->testMetadata->getGroups());
        $this->assertContains('windows', $this->testMetadata->getEnv());
    }

    public function testCommentedInBlockScenarioOptions(): void
    {
        $code = <<<EOF
<?php
/**
 * @skip
 */
EOF;
        $this->parser->parseScenarioOptions($code);
        $this->assertTrue($this->testMetadata->isBlocked());
    }

    public function testFeatureCommented(): void
    {
        $code = "<?php\n //\\\$I->wantTo('run this test'); ";
        $this->parser->parseFeature($code);
        $this->assertNull($this->scenario->getFeature());

        $code = "<?php\n /*\n \\\$I->wantTo('run this test'); \n */";
        $this->parser->parseFeature($code);
        $this->assertNull($this->scenario->getFeature());
    }

    public function testScenarioSkipOptionsHandled(): void
    {
        $code = "<?php\n // @skip pass along";
        $this->parser->parseScenarioOptions($code);
        $this->assertTrue($this->testMetadata->isBlocked());
    }

    public function testScenarioIncompleteOptionHandled(): void
    {
        $code = "<?php\n // @incomplete not ready yet";
        $this->parser->parseScenarioOptions($code);
        $this->assertTrue($this->testMetadata->isBlocked());
    }

    public function testSteps(): void
    {
        $code = file_get_contents(\Codeception\Configuration::projectDir().'tests/cli/UnitCept.php');
        $this->assertStringContainsString('$I->seeInThisFile', $code);
        $this->parser->parseSteps($code);
        $text = $this->scenario->getText();
        $this->assertStringContainsString("I see in this file", $text);
    }

    public function testStepsWithFriends(): void
    {
        $code = file_get_contents(codecept_data_dir('FriendsCept.php'));
        $this->assertStringContainsString('$I->haveFriend', $code);
        $this->parser->parseSteps($code);
        $text = $this->scenario->getText();
        $this->assertStringContainsString("jon does", $text);
        $this->assertStringContainsString("I have friend", $text);
        $this->assertStringContainsString("back to me", $text);
    }

    public function testParseFile(): void
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('SimpleTest.php'));
        $this->assertEquals(['SampleTest'], $classes);
    }

    public function testParseFileWithClass(): void
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('php55Test'));
        $this->assertEquals(['php55Test'], $classes);
    }

    public function testParseFileWithAnonymousClass(): void
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('php70Test'));
        $this->assertEquals(['php70Test'], $classes);
    }
    
    /*
     * https://github.com/Codeception/Codeception/issues/1779
     */
    public function testParseFileWhichUnsetsFileVariable(): void
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('unsetFile.php'));
        $this->assertEquals([], $classes);
    }

    /**
     * @group core
     * @throws \Codeception\Exception\TestParseException
     */
    public function testModernValidation(): void
    {
        $this->expectException('Codeception\Exception\TestParseException');
        Parser::load(codecept_data_dir('Invalid.php'));
    }

    /**
     * @group core
     */
    public function testClassesFromFile(): void
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('DummyClass.php'));
        $this->assertContains('DummyClass', $classes);
        $classes = Parser::getClassesFromFile(codecept_data_dir('SimpleWithDependencyInjectionCest.php'));
        $this->assertContains('simpleDI\\LoadedTestWithDependencyInjectionCest', $classes);
        $this->assertContains('simpleDI\\AnotherCest', $classes);
    }
}
