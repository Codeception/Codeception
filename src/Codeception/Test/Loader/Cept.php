<?php

declare(strict_types=1);

namespace Codeception\Test\Loader;

use Codeception\Test\Cept as CeptFormat;

use function basename;

class Cept implements LoaderInterface
{
    /**
     * @var CeptFormat[]
     */
    protected array $tests = [];

    public function getPattern(): string
    {
        return '~Cept\.php$~';
    }

    public function loadTests(string $filename): void
    {
        $name = basename($filename, 'Cept.php');

        $cept = new CeptFormat($name, $filename);
        $this->tests[] = $cept;
    }

    /**
     * @return CeptFormat[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }
}
