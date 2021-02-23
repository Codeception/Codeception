<?php

declare(strict_types=1);

use \Codeception\Stub;
use \Codeception\Stub\Expected;

class StubTest extends \Codeception\PHPUnit\TestCase
{
    /**
     * @var DummyClass
     */
    protected $dummy;

    public function _setUp(): void
    {
        $conf = \Codeception\Configuration::config();
        require_once $file = \Codeception\Configuration::dataDir().'DummyClass.php';
        $this->dummy = new DummyClass(true);
    }

    public function testMakeEmpty(): void
    {
        $dummy = Stub::makeEmpty('DummyClass');
        $this->assertInstanceOf('DummyClass', $dummy);
        $this->assertTrue(method_exists($dummy, 'helloWorld'));
        $this->assertNull($dummy->helloWorld());
    }

    public function testMakeEmptyMethodReplaced(): void
    {
        $dummy = Stub::makeEmpty('DummyClass', ['helloWorld' => function () {
            return 'good bye world';
        }]);
        $this->assertMethodReplaced($dummy);
    }

    public function testMakeEmptyMethodSimplyReplaced(): void
    {
        $dummy = Stub::makeEmpty('DummyClass', ['helloWorld' => 'good bye world']);
        $this->assertMethodReplaced($dummy);
    }

    public function testMakeEmptyExcept(): void
    {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'helloWorld');
        $this->assertEquals($this->dummy->helloWorld(), $dummy->helloWorld());
        $this->assertNull($dummy->goodByeWorld());
    }

    public function testMakeEmptyExceptPropertyReplaced(): void
    {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'getCheckMe', ['checkMe' => 'checked!']);
        $this->assertEquals('checked!', $dummy->getCheckMe());
    }

    public function testMakeEmptyExceptMagicalPropertyReplaced(): void
    {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'getCheckMeToo', ['checkMeToo' => 'checked!']);
        $this->assertEquals('checked!', $dummy->getCheckMeToo());
    }

    public function testFactory(): void
    {
        $dummies = Stub::factory('DummyClass', 2);
        $this->assertCount(2, $dummies);
        $this->assertInstanceOf('DummyClass', $dummies[0]);
    }

    public function testMake(): void
    {
        $dummy = Stub::make('DummyClass', ['goodByeWorld' => function () {
            return 'hello world';
        }]);
        $this->assertEquals($this->dummy->helloWorld(), $dummy->helloWorld());
        $this->assertEquals("hello world", $dummy->goodByeWorld());
    }

    public function testMakeMethodReplaced(): void
    {
        $dummy = Stub::make('DummyClass', ['helloWorld' => function () {
            return 'good bye world';
        }]);
        $this->assertMethodReplaced($dummy);
    }

    public function testMakeWithMagicalPropertiesReplaced(): void
    {
        $dummy = Stub::make('DummyClass', ['checkMeToo' => 'checked!']);
        $this->assertEquals('checked!', $dummy->checkMeToo);
    }

    public function testMakeMethodSimplyReplaced(): void
    {
        $dummy = Stub::make('DummyClass', ['helloWorld' => 'good bye world']);
        $this->assertMethodReplaced($dummy);
    }

    public function testCopy(): void
    {
        $dummy = Stub::copy($this->dummy, ['checkMe' => 'checked!']);
        $this->assertEquals('checked!', $dummy->getCheckMe());
        $dummy = Stub::copy($this->dummy, ['checkMeToo' => 'checked!']);
        $this->assertEquals('checked!', $dummy->getCheckMeToo());
    }

    public function testConstruct(): void
    {
        $dummy = Stub::construct('DummyClass', ['checkMe' => 'checked!']);
        $this->assertEquals('constructed: checked!', $dummy->getCheckMe());

        $dummy = Stub::construct(
            'DummyClass',
            ['checkMe' => 'checked!'],
            ['targetMethod' => function () {
                return false;
            }]
        );
        $this->assertEquals('constructed: checked!', $dummy->getCheckMe());
        $this->assertEquals(false, $dummy->targetMethod());
    }

    public function testConstructMethodReplaced(): void
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

    public function testConstructMethodSimplyReplaced(): void
    {
        $dummy = Stub::make('DummyClass', ['helloWorld' => 'good bye world']);
        $this->assertMethodReplaced($dummy);
    }

    public function testConstructEmpty(): void
    {
        $dummy = Stub::constructEmpty('DummyClass', ['checkMe' => 'checked!']);
        $this->assertNull($dummy->getCheckMe());
    }

    public function testConstructEmptyExcept(): void
    {
        $dummy = Stub::constructEmptyExcept('DummyClass', 'getCheckMe', ['checkMe' => 'checked!']);
        $this->assertNull($dummy->targetMethod());
        $this->assertEquals('constructed: checked!', $dummy->getCheckMe());
    }

    public function testUpdate(): void
    {
        $dummy = Stub::construct('DummyClass');
        Stub::update($dummy, ['checkMe' => 'done']);
        $this->assertEquals('done', $dummy->getCheckMe());
        Stub::update($dummy, ['checkMeToo' => 'done']);
        $this->assertEquals('done', $dummy->getCheckMeToo());
    }

    public function testStubsFromObject(): void
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

    protected function assertMethodReplaced($dummy): void
    {
        $this->assertTrue(method_exists($dummy, 'helloWorld'));
        $this->assertNotEquals($this->dummy->helloWorld(), $dummy->helloWorld());
        $this->assertEquals($dummy->helloWorld(), 'good bye world');
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
    public function testMockedMethodIsCalledFail($stubMarshaler, $failMessage): void
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

    private function thenWeMustCallMethodForException($mock): void
    {
        $mock->call();
    }

    private function thenWeDontCallAnyMethodForExceptionJustVerify($mock): void
    {
        $mock->__phpunit_verify();
        $this->fail('Expected exception');
    }

    private function thereAreNeverMatcher($stubMarshaler): bool
    {
        $matcher = $stubMarshaler->getMatcher();

        return 0 == $matcher->getInvocationCount();
    }

    private function resetMockObjects(): void
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
    public function testMethodMatcherWithMake($count, $matcher, $expected = false): void
    {
        $dummy = Stub::make('DummyClass', ['goodByeWorld' => $matcher], $this);

        $this->repeatCall($count, [$dummy, 'goodByeWorld'], $expected);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithMakeEmpty($count, $matcher): void
    {
        $dummy = Stub::makeEmpty('DummyClass', ['goodByeWorld' => $matcher], $this);

        $this->repeatCall($count, [$dummy, 'goodByeWorld']);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithMakeEmptyExcept($count, $matcher): void
    {
        $dummy = Stub::makeEmptyExcept('DummyClass', 'getCheckMe', ['goodByeWorld' => $matcher], $this);

        $this->repeatCall($count, [$dummy, 'goodByeWorld']);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithConstruct($count, $matcher): void
    {
        $dummy = Stub::construct('DummyClass', [], ['goodByeWorld' => $matcher], $this);

        $this->repeatCall($count, [$dummy, 'goodByeWorld']);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithConstructEmpty($count, $matcher): void
    {
        $dummy = Stub::constructEmpty('DummyClass', [], ['goodByeWorld' => $matcher], $this);

        $this->repeatCall($count, [$dummy, 'goodByeWorld']);
    }

    /**
     * @dataProvider matcherProvider
     */
    public function testMethodMatcherWithConstructEmptyExcept($count, $matcher): void
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

    private function repeatCall($count, $callable, $expected = false): void
    {
        for ($i = 0; $i < $count; $i++) {
            $actual = call_user_func($callable);
            if ($expected) {
                $this->assertEquals($expected, $actual);
            }
        }
    }

    public function testConsecutive(): void
    {
        $dummy = Stub::make('DummyClass', ['helloWorld' => Stub::consecutive('david', 'emma', 'sam', 'amy')]);

        $this->assertEquals('david', $dummy->helloWorld());
        $this->assertEquals('emma', $dummy->helloWorld());
        $this->assertEquals('sam', $dummy->helloWorld());
        $this->assertEquals('amy', $dummy->helloWorld());

        // Expected null value when no more values
        $this->assertNull($dummy->helloWorld());
    }

    public function testStubPrivateProperties(): void
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
        $this->assertEquals('gamma', $tester->getName());
        $this->assertEquals('randomstuff', $tester->getRandomName());
        $this->assertEquals('ticky2', $tester->getT());
    }

    public function testStubMakeEmptyInterface()
    {
        $stub = Stub::makeEmpty('\Countable', ['count' => 5]);
        $this->assertEquals(5, $stub->count());
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
