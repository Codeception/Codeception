<?php

declare(strict_types=1);

use Codeception\Config\Params;
use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\PHPUnit\TestCase;

final class ParamsTest extends TestCase
{
    private ReflectionProperty $property;

    private mixed $original;

    protected function setUp(): void
    {
        $this->property = new ReflectionProperty(Configuration::class, 'params');
        $this->original = $this->property->getValue();
    }

    protected function tearDown(): void
    {
        $this->property->setValue(null, $this->original);
    }

    public function testGetReturnsLoadedParam(): void
    {
        $this->property->setValue(null, ['TOKEN' => 'abc']);

        $this->assertSame('abc', Params::get('TOKEN'));
        $this->assertSame('fallback', Params::get('MISSING', 'fallback'));
    }

    public function testGetThrowsWhenParamsNotLoaded(): void
    {
        $this->property->setValue(null, null);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageMatches('/Params are not loaded/');
        Params::get('TOKEN');
    }
}
