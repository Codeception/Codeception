<?php

declare(strict_types=1);

use Codeception\Attribute\Group;
use Codeception\Lib\Parser;
use Codeception\Scenario;
use Codeception\Test\Cept;
use Codeception\Test\Metadata;
use Codeception\Test\Unit;

#[Group('core')]
final class ParserTest extends Unit
{
    protected \CodeGuy $tester;

    protected Parser $parser;

    protected Scenario $scenario;

    protected Metadata $testMetadata;

    protected function _before()
    {
        $cept = new Cept('demo', 'DemoCept.php');

        $this->testMetadata = $cept->getMetadata();
        $this->scenario = new Codeception\Scenario($cept);
        $this->parser = new Parser($this->scenario, $this->testMetadata);
    }

    public function testParsingFeature()
    {
        $code = "<?php\n \\\$I->wantTo('run this test'); ";
        $this->parser->parseFeature($code);
        $this->assertSame('run this test', $this->scenario->getFeature());

        $code = "<?php\n \\\$I->wantToTest('this run'); ";
        $this->parser->parseFeature($code);
        $this->assertSame('test this run', $this->scenario->getFeature());
    }

    public function testParsingWithWhitespace()
    {
        $code = "<?php\n \\\$I->wantTo( 'run this test' ); ";
        $this->parser->parseFeature($code);
        $this->assertSame('run this test', $this->scenario->getFeature());
    }

    public function testScenarioOptions()
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

    public function testCommentedInBlockScenarioOptions()
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

    public function testFeatureCommented()
    {
        $code = "<?php\n //\\\$I->wantTo('run this test'); ";
        $this->parser->parseFeature($code);
        $this->assertEmpty($this->scenario->getFeature());

        $code = "<?php\n /*\n \\\$I->wantTo('run this test'); \n */";
        $this->parser->parseFeature($code);
        $this->assertEmpty($this->scenario->getFeature());
    }

    public function testScenarioSkipOptionsHandled()
    {
        $code = "<?php\n // @skip pass along";
        $this->parser->parseScenarioOptions($code);
        $this->assertTrue($this->testMetadata->isBlocked());
    }

    public function testScenarioIncompleteOptionHandled()
    {
        $code = "<?php\n // @incomplete not ready yet";
        $this->parser->parseScenarioOptions($code);
        $this->assertTrue($this->testMetadata->isBlocked());
    }

    public function testSteps()
    {
        $code = file_get_contents(\Codeception\Configuration::projectDir() . 'tests/cli/UnitCept.php');
        $this->assertStringContainsString('$I->seeInThisFile', $code);
        $this->parser->parseSteps($code);
        $text = $this->scenario->getText();
        $this->assertStringContainsString("I see in this file", $text);
    }

    public function testStepsWithFriends()
    {
        $code = file_get_contents(codecept_data_dir('FriendsCept.php'));
        $this->assertStringContainsString('$I->haveFriend', $code);
        $this->parser->parseSteps($code);
        $text = $this->scenario->getText();
        $this->assertStringContainsString("jon does", $text);
        $this->assertStringContainsString("I have friend", $text);
        $this->assertStringContainsString("back to me", $text);
    }

    public function testParseFile()
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('SimpleTest.php'));
        $this->assertSame(['SampleTest'], $classes);
    }

    public function testParseFileWithClass()
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('php55Test'));
        $this->assertSame(['php55Test'], $classes);
    }

    public function testParseFileWithAnonymousClass()
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('php70Test'));
        $this->assertSame(['php70Test'], $classes);
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/1779
     */
    public function testParseFileWhichUnsetsFileVariable()
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('unsetFile.php'));
        $this->assertSame([], $classes);
    }

    #[Group('core')]
    public function testModernValidation()
    {
        $this->expectException(\Codeception\Exception\TestParseException::class);
        Parser::load(codecept_data_dir('Invalid.php'));
    }

    #[Group('core')]
    public function testClassesFromFile()
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('DummyClass.php'));
        $this->assertContains(\DummyClass::class, $classes);
        $classes = Parser::getClassesFromFile(codecept_data_dir('SimpleWithDependencyInjectionCest.php'));
        $this->assertContains('simpleDI\\LoadedTestWithDependencyInjectionCest', $classes);
        $this->assertContains('simpleDI\\AnotherCest', $classes);
    }

    #[Group('core')]
    public function testNamedParameterNamedClassIsNotClass()
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('namedParameter.php'));
        $this->assertEquals([], $classes);
    }

    #[Group('core')]
    public function testParseTestContainingAnnonymousClassWithAttribute()
    {
        $classes = Parser::getClassesFromFile(codecept_data_dir('AnonymousClassWithAttributeCest.php'));
        $this->assertEquals(['Tests\Unit\AnonymousClassWithAttributeCest'], $classes);
    }
}
