<?php
use \Codeception\Util\Stub;

class ParserTest extends \Codeception\TestCase\Test
{
    /**
     * @var \Codeception\Parser
     */
    private $parser;
    /**
     * @var \CodeGuy
     */
    protected $codeGuy;

    protected function _before()
    {
        $this->scenario = new \Codeception\Scenario(Stub::make('Codeception\TestCase\Cept'));
        $this->parser = new \Codeception\Parser($this->scenario);
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

    public function testScenarioOptions()
    {
        $code = "<?php\n \$scenario->group('davert'); \$scenario->env('windows');";
        $this->parser->parseScenarioOptions($code);
        $this->assertContains('davert', $this->scenario->getGroups());
        $this->assertContains('windows', $this->scenario->getEnv());

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
        $this->assertContains('I see in this file \'<testsuite name="unit" tests="5" assertions="5"', $text);
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
    
}