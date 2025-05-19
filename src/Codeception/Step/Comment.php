<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;

use function mb_strcut;

class Comment extends CodeceptionStep
{
    public function __toString(): string
    {
        return $this->getAction();
    }

    public function toString(int $maxLength): string
    {
        return mb_strcut((string)$this, 0, $maxLength, 'utf-8');
    }

    public function getHtml(string $highlightColor = '#732E81'): string
    {
        return '<strong>' . $this->getAction() . '</strong>';
    }

    public function getPhpCode(int $maxLength): string
    {
        return '// ' . $this->getAction();
    }

    public function run(?ModuleContainer $container = null): void
    {
        // no-op
    }

    public function getPrefix(): string
    {
        return '';
    }
}
