<?php

declare(strict_types=1);

namespace Codeception\Lib;

use function file_exists;
use function rtrim;

final class ConfigFileLocator
{
    private function __construct(
        public readonly string $distFile,
        public readonly string $mainFile,
    ) {
    }

    public static function locate(string $dir, string $stem): self
    {
        $base  = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $stem;
        $isPhp = file_exists($base . '.php') || file_exists($base . '.dist.php');
        [$dist, $main] = $isPhp ? ['.dist.php', '.php'] : ['.dist.yml', '.yml'];

        return new self($base . $dist, $base . $main);
    }
}
