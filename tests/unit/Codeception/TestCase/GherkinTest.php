<?php
class GherkinTest extends PHPUnit_Framework_TestCase
{

    protected $feature;
    public static $calls = '';

    /**
     * @var \Codeception\Test\Loader\Gherkin
     */
    protected $loader;

    protected function setUp()
    {
        $this->loader = new \Codeception\Test\Loader\Gherkin([
            'contexts' => [
                'default' => ['GherkinTestContext']

            ]
        ]);
        self::$calls = '';
    }

    public function testLoadGherkin()
    {
        $this->loader->loadTests(codecept_data_dir('refund.feature'));
        $tests = $this->loader->getTests();
        $this->assertCount(1, $tests);
        /** @var $test \Codeception\Test\Format\Gherkin  **/
        $test = $tests[0];
        $this->assertInstanceOf('\Codeception\Test\Format\Gherkin', $test);
        $this->assertEquals('Jeff returns a faulty microwave', $test->getFeature());
    }

    /**
     * @depends testLoadGherkin
     */
    public function testLoadWithContexts()
    {
        $this->loader->loadTests(codecept_data_dir('refund.feature'));
        $test = $this->loader->getTests()[0];
        /** @var $test \Codeception\Test\Format\Gherkin  **/
        $test->getMetadata()->setServices([
            'di' => new \Codeception\Lib\Di()
        ]);
        $test->test();
        $this->assertEquals('abc', self::$calls);
    }

    public function testTags()
    {
        $this->loader = new \Codeception\Test\Loader\Gherkin([
            'contexts' => [
                'default' => ['GherkinTestContext'],
                'tag' => [
                    'important' => ['TagGherkinContext']
                ]
            ]
        ]);
        $this->loader->loadTests(codecept_data_dir('refund.feature'));
        $test = $this->loader->getTests()[0];
        /** @var $test \Codeception\Test\Format\Gherkin  **/
        $test->getMetadata()->setServices([
            'di' => new \Codeception\Lib\Di()
        ]);
        $test->test();
        $this->assertEquals('aXc', self::$calls);

    }
}

class GherkinTestContext {

    /**
     * @Given Jeff has bought a microwave for :param
     */
    public function hasBoughtMicrowave()
    {
        GherkinTest::$calls .= 'a';
    }

    /**
     * @When /he returns the microwave/
     */
    public function heReturns()
    {
        GherkinTest::$calls .= 'b';
    }

    /**
     * @Then Jeff should be refunded $100
     */
    public function beRefunded()
    {
        GherkinTest::$calls .= 'c';
    }
}

class TagGherkinContext {


    /**
     * @When he returns the microwave
     */
    public function heReturns()
    {
        GherkinTest::$calls .= 'X';
    }

}