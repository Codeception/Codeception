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
        };

        $signature = Descriptor::getTestSignatureUnique($testCase);
        $this->assertSame($testCase->getSignature() . ':41e8901', $signature);
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
        };

        $signature = Descriptor::getTestSignatureUnique($testCase);
        $this->assertSame($testCase->getSignature() . ':f863384', $signature);
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
        };

        $signature = Descriptor::getTestSignatureUnique($testCase);
        $this->assertSame($testCase->getSignature() . ':5cf307a', $signature);
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
                            'array' => [
                                'one',
                                'two',
                                'three' => [
                                    'key' => 'value',
                                    'enum1' => UnitEnumExample::FOO,
                                    'enum2' => BackedEnumExample::BAR,
                                ]
                            ],
                        ];
                    }
                };
            }
        };

        $signature = Descriptor::getTestSignatureUnique($testCase);
        $this->assertSame($testCase->getSignature() . ':e3d81e2', $signature);
    }
}