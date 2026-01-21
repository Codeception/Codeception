<?php

declare(strict_types=1);

namespace unit\Codeception\Test;

use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\Descriptive;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

enum UnitEnumExample
{
    case FOO;
    case BAR;
}

enum BackedEnumExample: string
{
    case FOO = 'one';
    case BAR = 'two';
}

class TestCase implements Descriptive
{
    public function toString(): string
    {
        return 'TestCase';
    }

    public function getFileName(): string
    {
        return __FILE__;
    }

    public function getSignature(): string
    {
        return 'TestCaseSignature';
    }
}

class DescriptorTest extends PHPUnitTestCase
{
    public function testUnitEnumSerialization(): void
    {
        $testCase = new class extends TestCase {
            public function getMetadata(): object
            {
                return new class {
                    public function getCurrent(string $key): mixed
                    {
                        return ['enum' => UnitEnumExample::FOO];
                    }
                };
            }

            public function getSignature(): string
            {
                return 'TestCaseMethod';
            }
        };

        $signature = Descriptor::getTestSignatureUnique($testCase);
        $this->assertSame('TestCaseMethod' . ':41e8901', $signature);
    }

    public function testBackedEnumSerialization(): void
    {
        $testCase = new class extends TestCase {
            public function getMetadata(): object
            {
                return new class {
                    public function getCurrent(string $key): mixed
                    {
                        return ['enum' => BackedEnumExample::FOO];
                    }
                };
            }

            public function getSignature(): string
            {
                return 'TestCaseMethod';
            }
        };

        $signature = Descriptor::getTestSignatureUnique($testCase);
        $this->assertSame('TestCaseMethod:f863384', $signature);
    }


    public function testNestedEnumSerialization(): void
    {
        $testCase = new class extends TestCase {
            public function getMetadata(): object
            {
                return new class {
                    public function getCurrent(string $key): mixed
                    {
                        return [
                            'unit' => UnitEnumExample::BAR,
                            'backed' => BackedEnumExample::BAR,
                            'nested' => ['enum' => UnitEnumExample::FOO]
                        ];
                    }
                };
            }

            public function getSignature(): string
            {
                return 'TestCaseMethod';
            }
        };

        $signature = Descriptor::getTestSignatureUnique($testCase);
        $this->assertSame('TestCaseMethod:db6e561', $signature);
    }

    public function testStringSerialization(): void
    {
        $testCase = new class extends TestCase {
            public function getMetadata(): object
            {
                return new class {
                    public function getCurrent(string $key): mixed
                    {
                        return ['string' => 'test value'];
                    }
                };
            }

            public function getSignature(): string
            {
                return 'TestCaseMethod';
            }
        };

        $signature = Descriptor::getTestSignatureUnique($testCase);
        $this->assertSame('TestCaseMethod:5cf307a', $signature);
    }

    public function testArraySerialization(): void
    {
        $testCase = new class extends TestCase {
            public function getMetadata(): object
            {
                return new class {
                    public function getCurrent(string $key): mixed
                    {
                        return [
                            'array' => ['one', 'two', 'three'],
                            'nested' => ['key' => 'value']
                        ];
                    }
                };
            }

            public function getSignature(): string
            {
                return 'TestCaseMethod';
            }
        };

        $signature = Descriptor::getTestSignatureUnique($testCase);
        $this->assertSame('TestCaseMethod:020e182', $signature);
    }
}