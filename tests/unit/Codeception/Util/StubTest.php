<?php

declare(strict_types=1);

use Codeception\Stub;
use Codeception\Stub\Expected;

class StubTest extends \Codeception\PHPUnit\TestCase
{
    /**
     * @var DummyClass
     */
    protected $dummy;

    public function _setUp()
    {
        $conf = \Codeception\Configuration::config();
        require_once $file = \Codeception\Configuration::dataDir().'DummyClass.php';
        $this->dummy = new DummyClass(true);
    }

    public function testMakeEmpty()
    {
        $dummy = Stub::makeEmpty('DummyClass');
        $this->assertInstanceOf('DummyClass', $dummy);
        $this->assertTrue(method_exists($dummy, 'helloWorld'));
        $this->assertNull($dummy->helloWorld());
    }

    public function testMakeEmptyMethodReplaced()
    {
        $dummy = Stub::makeEmpty('DummyClass', ['helloWorld' => function () {
            return 'good bye world';
        }]);
        $this->assertMethodReplaced($dummy);
    }

    public function testMakeEmptyMethodSimplyReplaced()
    {
        $dummy = Stub::makeEmpty('DummyClass', ['helloWorld' => 'good bye world']);
        $this->assertMethodReplaced($dummy);
    }

    public function testMakeEmptyExcept()
    {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'helloWorld');
        $this->assertSame($this->dummy->helloWorld(), $dummy->helloWorld());
        $this->assertNull($dummy->goodByeWorld());
    }

    public function testMakeEmptyExceptPropertyReplaced()
    {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'getCheckMe', ['checkMe' => 'checked!']);
        $this->assertSame('checked!', $dummy->getCheckMe());
    }

    public function testMakeEmptyExceptMagicalPropertyReplaced()
    {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'getCheckMeToo', ['checkMeToo' => 'checked!']);
        $this->assertSame('checked!', $dummy->getCheckMeToo());
    }

    public function testFactory()
    {
        $dummies = Stub::factory('DummyClass', 2);
        $this->assertCount(2, $dummies);
        $this->assertInstanceOf('DummyClass', $dummies[0]);
    }

    public function testMake()
    {
        $dummy = Stub::make('DummyClass', ['goodByeWorld' => function () {
            return 'hello world';
        }]);
        $this->assertSame($this->dummy->helloWorld(), $dummy->helloWorld());
        $this->assertSame("hello world", $dummy->goodByeWorld());
    }

    public function testMakeMethodReplaced()
    {
        $dummy = Stub::make('DummyClass', ['helloWorld' => function () {
            return 'good bye world';
        }]);
        $this->assertMethodReplaced($dummy);
    }

    public function testMakeWithMagicalPropertiesReplaced()
    {
        $dummy = Stub::make('DummyClass', ['checkMeToo' => 'checked!']);
        $this->assertSame('checked!', $dummy->checkMeToo);
    }

    public function testMakeMethodSimplyReplaced()
    {
        $dummy = Stub::make('DummyClass', ['helloWorld' => 'good bye world']);
        $this->assertMethodReplaced($dummy);
    }

    public function testCopy()
    {
        $dummy = Stub::copy($this->dummy, ['checkMe' => 'checked!']);
        $this->assertSame('checked!', $dummy->getCheckMe());
        $dummy = Stub::copy($this->dummy, ['checkMeToo' => 'checked!']);
        $this->assertSame('checked!', $dummy->getCheckMeToo());
    }

    public function testConstruct()
    {
        $dummy = Stub::construct('DummyClass', ['checkMe' => 'checked!']);
        $this->assertSame('constructed: checked!', $dummy->getCheckMe());

        $dummy = Stub::construct(
            'DummyClass',
            ['checkMe' => 'checked!'],
            ['targetMethod' => function () {
                return false;
            }]
        );
        $this->assertSame('constructed: checked!', $dummy->getCheckMe());
        $this->assertSame(false, $dummy->targetMethod());
    }

    public function testConstructMethodReplaced()
    {
        $dummy = Stub::construct(
            'DummyClass',
            [],
            ['helloWorld' => function () {
                return 'good bye world';
            }]
        );
        $this->assertMethodReplaced($dummy);
    }

    public function testConstructMethodSimplyReplaced()
    {
        $dummy = Stub::make('DummyClass', ['helloWorld' => 'good bye world']);
        $this->assertMethodReplaced($dummy);
    }

    public function testConstructEmpty()
    {
        $dummy = Stub::constructEmpty('DummyClass', ['checkMe' => 'checked!']);
        $this->assertNull($dummy->getCheckMe());
    }

    public function testConstructEmptyExcept()
    {
        $dummy = Stub::constructEmptyExcept('DummyClass', 'getCheckMe', ['checkMe' => 'checked!']);
        $this->assertNull($dummy->targetMethod());
        $this->assertSame('constructed: checked!', $dummy->getCheckMe());
    }

    public function testUpdate()
    {
        $dummy = Stub::construct('DummyClass');
        Stub::update($dummy, ['checkMe' => 'done']);
        $this->assertSame('done', $dummy->getCheckMe());
        Stub::update($dummy, ['checkMeToo' => 'done']);
        $this->assertSame('done', $dummy->getCheckMeToo());
    }

    public function testStubsFromObject()
    {
        $dummy = Stub::make(new \DummyClass());
        $this->assertInstanceOf(
            '\PHPUnit\Framework\MockObject\MockObject',
            $dummy
        );
        $dummy = Stub::make(new \DummyOverloadableClass());
        $this->assertObjectHasAttribute('__mocked', $dummy);
        $dummy = Stub::makeEmpty(new \DummyClass());
        $this->assertInstanceOf(
            '\PHPUnit\Framework\MockObject\MockObject',
            $dummy
        );
        $dummy = Stub::makeEmpty(new \DummyOverloadableClass());
        $this->assertObjectHasAttribute('__mocked', $dummy);
        $dummy = Stub::makeEmptyExcept(new \DummyClass(), 'helloWorld');
        $this->assertInstanceOf(
            '\PHPUnit\Framework\MockObject\MockObject',
            $dummy
        );
        $dummy = Stub::makeEmptyExcept(new \DummyOverloadableClass(), 'helloWorld');
        $this->assertObjectHasAttribute('__mocked', $dummy);
        $dummy = Stub::construct(new \DummyClass());
        $this->assertInstanceOf(
            '\PHPUnit\Framework\MockObject\MockObject',
            $dummy
        );
        $dummy = Stub::construct(new \DummyOverloadableClass());
        $this->assertObjectHasAttribute('__mocked', $dummy);
        $dummy = Stub::constructEmpty(new \DummyClass());
        $this->assertInstanceOf(
            '\PHPUnit\Framework\MockObject\MockObject',
            $dummy
        );
        $dummy = Stub::constructEmpty(new \DummyOverloadableClass());
        $this->assertObjectHasAttribute('__mocked', $dummy);
        $dummy = Stub::constructEmptyExcept(new \DummyClass(), 'helloWorld');
        $this->assertInstanceOf(
            '\PHPUnit\Framework\MockObject\MockObject',
            $dummy
        );
        $dummy = Stub::constructEmptyExcept(new \DummyOverloadableClass(), 'helloWorld');
        $this->assertObjectHasAttribute('__mocked', $dummy);
    }

    protected function assertMethodReplaced($dummy)
    {
        $this->assertTrue(method_exists($dummy, 'helloWorld'));
        $this->assertNotSame($this->dummy->helloWorld(), $dummy->helloWorld());
        $this->assertSame($dummy->helloWorld(), 'good bye world');
    }

    public static function matcherAndFailMessageProvider(): array
    {
        return [
            [Expected::never(),
              "DummyClass::targetMethod() was not expected to be called."
            ],
            [Expected::atLeastOnce(),
              "Expectation failed for method name is equal to <string:targetMethod> when invoked at least once.\n"
              . 'Expected invocation at least once but it never occured.'
            ],
            [Expected::once(),
              "Expectation failed for method name is equal to <string:targetMethod> when invoked 1 time(s).\n"
              . 'Method was expected to be called 1 times, actually called 0 times.'
            ],
            [Expected::exactly(1),
              "Expectation failed for method name is equal to <string:targetMethod> when invoked 3 time(s).\n"
              . 'Method was expected to be called 3 times, actually called 0 times.'
            ],
            [Expected::exactly(3),
              "Expectation failed for method name is equal to <string:targetMethod> when invoked 3 time(s).\n"
              . 'Method was expected to be called 3 times, actually called 0 times.'
            ],
        ];
    }

    /**
     * @dataProvider matcherAndFailMessageProvider
     */
    public function testMockedMethodIsCalledFail($stubMarshaler, $failMessage)
    {
        $mock = Stub::makeEmptyExcept('DummyClass', 'call', ['targetMethod' => $stubMarshaler], $this);
        $mock->goodByeWorld();

        try {
            if ($this->thereAreNeverMatcher($stubMarshaler)) {
                $this->thenWeMustCallMethodForException($mock);
            } else {
                $this->thenWeDontCallAnyMethodForExceptionJustVerify($mock);
            }
        } catch (PHPUnit\Framework\ExpectationFailedException $e) {
            $this->assertSame($failMessage, $e->getMessage());
        }

        $this->resetMockObjects();
    }

    private function thenWeMustCallMethodForException($mock)
    {
        $mock->call();
    }

    private function thenWeDontCallAnyMethodForExceptionJustVerify($mock)
    {
        $mock->__phpunit_verify();
        $this->fail('Expected exception');
    }

    private function thereAreNeverMatcher($stubMarshaler): bool
    {
        $matcher = $stubMarshaler->getMatcher();

        return 0 == $matcher->getInvocationCount();
    }

    private function resetMockObjects()
    {
        $refl = new ReflectionObject($this);
        $refl = $refl->getParentClass()->getParentClass();
        $prop = $refl->getProperty('mockObjects');
        $prop->setAccessible(true);
        $prop->setValue($this, []);
    }

    public static function matcherProvider(): array
    {
        return [
            [0, Expected::never()],
            [1, Expected::once()],
            [2, Expected::atLeastOnce()],
            [3, Expected::exactly(3)],
            [1, Expected::once(function () {
                return true;
            }), true],
            [2, Expected::atLeastOnce(function () {
                return [];
            }), []],
            [1, Expected::exactly(1, function () {
                return null;
            }), null],
            [1, Expected::exactly(1, function () {
                return 'hello world!';
            }), 'hello world!'],
        ];
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithMake($count, $matcher, $expected = false)
    {
        $dummy = Stub::make('DummyClass', ['goodByeWorld' => $matcher], $this);

        $this->repeatCall($count, [$dummy, 'goodByeWorld'], $expected);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithMakeEmpty($count, $matcher)
    {
        $dummy = Stub::makeEmpty('DummyClass', ['goodByeWorld' => $matcher], $this);

        $this->repeatCall($count, [$dummy, 'goodByeWorld']);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithMakeEmptyExcept($count, $matcher)
    {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'getCheckMe', ['goodByeWorld' => $matcher], $this);

        $this->repeatCall($count, [$dummy, 'goodByeWorld']);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithConstruct($count, $matcher)
    {
        $dummy = Stub::construct('DummyClass', [], ['goodByeWorld' => $matcher], $this);

        $this->repeatCall($count, [$dummy, 'goodByeWorld']);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithConstructEmpty($count, $matcher)
    {
        $dummy = Stub::constructEmpty('DummyClass', [], ['goodByeWorld' => $matcher], $this);

        $this->repeatCall($count, [$dummy, 'goodByeWorld']);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithConstructEmptyExcept($count, $matcher)
    {
        $dummy = Stub::constructEmptyExcept(
            'DummyClass',
            'getCheckMe',
            [],
            ['goodByeWorld' => $matcher],
            $this
        );

        $this->repeatCall($count, [$dummy, 'goodByeWorld']);
    }

    private function repeatCall($count, $callable, $expected = false)
    {
        for ($i = 0; $i < $count; $i++) {
            $actual = call_user_func($callable);
            if ($expected) {
                $this->assertSame($expected, $actual);
            }
        }
    }

    public function testConsecutive()
    {
        $dummy = Stub::make('DummyClass', ['helloWorld' => Stub::consecutive('david', 'emma', 'sam', 'amy')]);

        $this->assertSame('david', $dummy->helloWorld());
        $this->assertSame('emma', $dummy->helloWorld());
        $this->assertSame('sam', $dummy->helloWorld());
        $this->assertSame('amy', $dummy->helloWorld());

        // Expected null value when no more values
        $this->assertNull($dummy->helloWorld());
    }

    public function testStubPrivateProperties()
    {
        $tester = Stub::construct(
            'MyClassWithPrivateProperties',
            ['name' => 'gamma'],
            [
                 'randomName' => 'chicken',
                 't' => 'ticky2',
                 'getRandomName' => function () {
                     return "randomstuff";
                 }
            ]
        );
        $this->assertSame('gamma', $tester->getName());
        $this->assertSame('randomstuff', $tester->getRandomName());
        $this->assertSame('ticky2', $tester->getT());
    }

    public function testStubMakeEmptyInterface()
    {
        $stub = Stub::makeEmpty('\Countable', ['count' => 5]);
        $this->assertSame(5, $stub->count());
    }
}

class MyClassWithPrivateProperties
{
    private $name;
    /**
     * @var string
     */
    private $randomName = "gaia";
    /**
     * @var string
     */
    private $t = "ticky";

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRandomName(): string
    {
        return $this->randomName;
    }

    public function getT(): string
    {
        return $this->t;
    }
}
