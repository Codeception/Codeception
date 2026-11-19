<?php

declare(strict_types=1);

use Codeception\Lib\SelfUpdate\GithubReleaseStrategy;
use Codeception\Test\Unit;

final class GithubReleaseStrategyTest extends Unit
{
    private const RELEASES = [
        ['version' => '6.0.0', 'require' => ['php' => '^8.4']],
        ['version' => '6.0.0-RC1', 'require' => ['php' => '^8.4']],
        ['version' => '5.10.0-beta', 'require' => ['php' => '^8.2']],
        ['version' => '5.4.1', 'require' => ['php' => '^8.3']],
        ['version' => '5.4.0', 'require' => ['php' => '^8.3']],
        ['version' => '5.3.7', 'require' => ['php' => '^8.2']],
        ['version' => '5.3.6', 'require' => ['php' => '^8.2']],
        ['version' => '5.3.5', 'require' => ['php' => '^8.2']],
        ['version' => '50.0.0'],
        ['version' => '4.2.2', 'require' => ['php' => '>=5.6.0 <9.0']],
        ['version' => '4.2.1', 'require' => ['php' => '>=5.6.0 <9.0']],
    ];

    public function testFindsNewerStableReleasesOfSameMajorVersionNewestFirst(): void
    {
        $this->assertSame(['5.4.1', '5.4.0', '5.3.7', '5.3.6'], GithubReleaseStrategy::findUpdates(self::RELEASES, '5.3.5', '8.4.0'));
        $this->assertSame(['4.2.2'], GithubReleaseStrategy::findUpdates(self::RELEASES, '4.2.1', '8.4.0'));
    }

    public function testSkipsReleasesRequiringNewerPhp(): void
    {
        $this->assertSame(['5.3.7', '5.3.6'], GithubReleaseStrategy::findUpdates(self::RELEASES, '5.3.5', '8.2.10'));
    }

    public function testFindsNothingIfThereIsNoNewerRelease(): void
    {
        $this->assertSame([], GithubReleaseStrategy::findUpdates(self::RELEASES, '5.4.1', '8.4.0'));
        $this->assertSame([], GithubReleaseStrategy::findUpdates(self::RELEASES, '5.4.2', '8.4.0'));
        $this->assertSame([], GithubReleaseStrategy::findUpdates(self::RELEASES, '7.0.0', '8.4.0'));
    }

    public function testExpandsMinifiedPackagistMetadata(): void
    {
        $minified = [
            ['version' => '5.3.7', 'require' => ['php' => '^8.2'], 'suggest' => ['ext-xdebug' => '']],
            ['version' => '5.3.6'],
            ['version' => '5.2.2', 'require' => ['php' => '^8.1'], 'suggest' => '__unset'],
        ];

        $this->assertSame([
            ['version' => '5.3.7', 'require' => ['php' => '^8.2'], 'suggest' => ['ext-xdebug' => '']],
            ['version' => '5.3.6', 'require' => ['php' => '^8.2'], 'suggest' => ['ext-xdebug' => '']],
            ['version' => '5.2.2', 'require' => ['php' => '^8.1']],
        ], GithubReleaseStrategy::expandMinified($minified));
    }
}
