<?php

declare(strict_types=1);

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Codeception\Exception\ParseException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Stub;
use Codeception\Test\Loader\Gherkin as GherkinLoader;
use Codeception\Test\Unit;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[Group('gherkin')]
final class GherkinTest extends Unit
{
    protected \CodeGuy $tester;

    protected $feature;

    public static string $calls = '';

    protected GherkinLoader $loader;

    protected function _before()
    {
        $this->loader = new GherkinLoader(
            [
                'gherkin' => [
                    'contexts' => [
                        'default' => ['GherkinTestContext']

                    ]
                ]
            ]
        );
        self::$calls = '';
    }

    protected function getServices(): array
    {
        return [
            'di'         => new Di(),
            'dispatcher' => Stub::makeEmpty(EventDispatcher::class),
            'modules'    => Stub::makeEmpty(ModuleContainer::class)
        ];
    }

    public function testLoadGherkin()
    {
        $this->loader->loadTests(codecept_data_dir('refund.feature'));
        $tests = $this->loader->getTests();
        $this->assertCount(1, $tests);
        $test = $tests[0];
        $this->assertInstanceOf(\Codeception\Test\Gherkin::class, $test);
        $this->assertSame('Refund item', $test->getFeature());
    }

    public function testGherkinScenario()
    {
        $this->loader->loadTests(codecept_data_dir('refund.feature'));
        $tests = $this->loader->getTests();
        $this->assertCount(1, $tests);
        $test = $tests[0];
        $this->assertInstanceOf(\Codeception\Test\Gherkin::class, $test);
        $this->assertSame('Jeff returns a faulty microwave', $test->getScenarioTitle());
    }

    #[Depends('testLoadGherkin')]
    public function testLoadWithContexts()
    {
        $this->loader->loadTests(codecept_data_dir('refund.feature'));
        $test = $this->loader->getTests()[0];
        $test->getMetadata()->setServices($this->getServices());
        $test->test();
        $this->assertSame('abc', self::$calls);
    }

    public function testBadRegex()
    {
        $this->expectException(ParseException::class);

        $this->loader = new GherkinLoader(
            [
                'gherkin' => [
                    'contexts' => [
                        'default' => ['GherkinInvalidContext'],
                    ]
                ]
            ]
        );
        $this->loader->loadTests(codecept_data_dir('refund.feature'));

        $test = $this->loader->getTests()[0];
        $test->getMetadata()->setServices($this->getServices());
        $test->test();
    }

    public function testTags()
    {
        $this->loader = new GherkinLoader(
            [
                'gherkin' => [
                    'contexts' => [
                        'default' => ['GherkinTestContext'],
                        'tag'     => [
                            'important' => ['TagGherkinContext']
                        ]
                    ]
                ]
            ]
        );
        $this->loader->loadTests(codecept_data_dir('refund.feature'));

        $test = $this->loader->getTests()[0];
        $test->getMetadata()->setServices($this->getServices());
        $test->test();
        $this->assertSame('aXc', self::$calls);
    }

    public function testRoles()
    {
        $this->loader = new GherkinLoader(
            [
                'gherkin' => [
                    'contexts' => [
                        'default' => ['GherkinTestContext'],
                        'role'     => [
                            'customer' => ['TagGherkinContext']
                        ]
                    ]
                ]
            ]
        );
        $this->loader->loadTests(codecept_data_dir('refund.feature'));

        $test = $this->loader->getTests()[0];
        $test->getMetadata()->setServices($this->getServices());
        $test->test();
        $this->assertSame('aXc', self::$calls);
    }


    public function testMatchingPatterns()
    {
        $pattern = 'hello :name, are you from :place?';
        $regex = $this->loader->makePlaceholderPattern($pattern);
        $this->assertMatchesRegularExpression($regex, 'hello "davert", are you from "kiev"?');
        $this->assertDoesNotMatchRegularExpression($regex, 'hello davert, are you from "kiev"?');

        $pattern = 'hello ":name", how are you';
        $regex = $this->loader->makePlaceholderPattern($pattern);
        $this->assertMatchesRegularExpression($regex, 'hello "davert", how are you');
        $this->assertDoesNotMatchRegularExpression($regex, 'hello "davert", are you from "kiev"?');

        $pattern = 'there should be :num cow(s)';
        $regex = $this->loader->makePlaceholderPattern($pattern);
        $this->assertMatchesRegularExpression($regex, 'there should be "1" cow');
        $this->assertMatchesRegularExpression($regex, 'there should be "5" cows');
        $this->assertMatchesRegularExpression($regex, 'there should be 1000 cows');
    }

    public function testGherkinCurrencySymbols()
    {
        $pattern = 'I have :money in my pocket';
        $regex = $this->loader->makePlaceholderPattern($pattern);
        $this->assertMatchesRegularExpression($regex, 'I have 3.5$ in my pocket');
        $this->assertMatchesRegularExpression($regex, 'I have 3.5€ in my pocket');
        $this->assertMatchesRegularExpression($regex, 'I have $3.5 in my pocket');
        $this->assertMatchesRegularExpression($regex, 'I have £3.5 in my pocket');
        $this->assertMatchesRegularExpression($regex, 'I have "35.10" in my pocket');
        $this->assertMatchesRegularExpression($regex, 'I have 5 in my pocket');
        $this->assertMatchesRegularExpression($regex, 'I have 5.1 in my pocket');

        $this->assertDoesNotMatchRegularExpression($regex, 'I have 3.5 $ in my pocket');
        $this->assertDoesNotMatchRegularExpression($regex, 'I have 3.5euro in my pocket');

        // Issue #3156
        $pattern = "there is a :arg1 product witch costs :arg2 €";
        $regex = $this->loader->makePlaceholderPattern($pattern);
        $this->assertMatchesRegularExpression($regex, 'there is a "football ball" product witch costs "1,5" €');
    }

    public function testMatchingEscapedPatterns()
    {
        $pattern = 'use password ":pass"';
        $regex = $this->loader->makePlaceholderPattern($pattern);
        $this->assertMatchesRegularExpression($regex, 'use password "fref\"fr"');
    }

    /**
     * @Issue #3051
     */
    public function testSimilarSteps()
    {
        $pattern = 'there is a User called :arg1';
        $regex = $this->loader->makePlaceholderPattern($pattern);
        $this->assertMatchesRegularExpression($regex, 'there is a User called "John"');
        $this->assertDoesNotMatchRegularExpression($regex, 'there is a User called "John" and surname "Smith"');
    }

    public function testMultipleSteps()
    {
        $patterns = array_keys($this->loader->getSteps()['default']);
        $this->assertContains('#^he returns the microwave$#u', $patterns);
        $this->assertContains('#^microwave is brought back$#u', $patterns);
    }
}

class GherkinTestContext
{
    /**
     * @Given Jeff has bought a microwave for :param
     */
    public function hasBoughtMicrowave()
    {
        GherkinTest::$calls .= 'a';
    }

    /**
     * @When he returns the microwave
     * @Then microwave is brought back
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

class TagGherkinContext
{
    /**
     * @When he returns the microwave
     */
    public function heReturns()
    {
        GherkinTest::$calls .= 'X';
    }
}

class GherkinInvalidContext
{
    /**
     * @Given /I (?:use:am connected to) the database (?db:.+)/i
     */
    public function notWorks()
    {
    }
}
