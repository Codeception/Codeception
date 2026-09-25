<?php

declare(strict_types=1);

use Codeception\Config\ConfigInterface;
use Codeception\Config\SuiteConfig;
use Codeception\PHPUnit\TestCase;

final class SuiteConfigTest extends TestCase
{
    public function testImplementsContract(): void
    {
        $this->assertInstanceOf(ConfigInterface::class, SuiteConfig::create());
    }

    public function testBuildsExpectedArray(): void
    {
        $config = SuiteConfig::create()
            ->actor('FunctionalTester')
            ->module('Asserts')
            ->module('Symfony', ['app_path' => 'src', 'environment' => 'test'])
            ->module('Doctrine', ['cleanup' => true], depends: 'Symfony')
            ->stepDecorators(null)
            ->toArray();

        $this->assertSame('FunctionalTester', $config['actor']);
        $this->assertSame([
            'Asserts',
            ['Symfony' => ['app_path' => 'src', 'environment' => 'test']],
            ['Doctrine' => ['cleanup' => true, 'depends' => 'Symfony']],
        ], $config['modules']['enabled']);
        $this->assertNull($config['step_decorators']);
    }

    public function testDependsAcceptsList(): void
    {
        $config = SuiteConfig::create()
            ->module('Doctrine', depends: ['Symfony', 'Db'])
            ->toArray();

        $this->assertSame([['Doctrine' => ['depends' => ['Symfony', 'Db']]]], $config['modules']['enabled']);
    }

    public function testDependsConflictIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SuiteConfig::create()->module('Doctrine', ['depends' => 'A'], depends: 'B');
    }

    public function testErrorLevelAcceptsNativeConstant(): void
    {
        $config = SuiteConfig::create()->errorLevel(E_ALL & ~E_DEPRECATED)->toArray();

        $this->assertSame(E_ALL & ~E_DEPRECATED, $config['error_level']);
    }

    public function testShuffleErrorLevelAndDeprecations(): void
    {
        $config = SuiteConfig::create()
            ->shuffle()
            ->errorLevel('E_ALL')
            ->convertDeprecationsToExceptions()
            ->toArray();

        $this->assertTrue($config['shuffle']);
        $this->assertSame('E_ALL', $config['error_level']);
        $this->assertTrue($config['convert_deprecations_to_exceptions']);
    }

    public function testEnvOverlayIsNormalized(): void
    {
        $config = SuiteConfig::create()
            ->actor('X')
            ->env('staging', SuiteConfig::create()->module('Db', ['dsn' => 'stage']))
            ->toArray();

        $this->assertSame(
            ['modules' => ['enabled' => [['Db' => ['dsn' => 'stage']]]]],
            $config['env']['staging']
        );
    }

    public function testMergeMergesRawKeys(): void
    {
        $config = SuiteConfig::create()
            ->actor('X')
            ->merge(['groups' => ['g' => ['a']]])
            ->toArray();

        $this->assertSame('X', $config['actor']);
        $this->assertSame(['g' => ['a']], $config['groups']);
    }
}
