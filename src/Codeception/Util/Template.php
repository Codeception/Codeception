<?php

declare(strict_types=1);

namespace Codeception\Util;

use function array_key_exists;
use function explode;
use function is_array;
use function preg_quote;
use function preg_replace_callback;
use function sprintf;
use function strval;

/**
 * Basic template engine used for generating initial Cept/Cest/Test files.
 */
class Template
{
    private array $vars = [];
    private readonly string $regex;

    public function __construct(
        private readonly string $template,
        private readonly string $placeholderStart = '{{',
        private readonly string $placeholderEnd = '}}',
        private readonly ?string $encoderFunction = null,
    ) {
        $this->regex = sprintf(
            '~%s([\w\.]+)%s~',
            preg_quote($this->placeholderStart, '~'),
            preg_quote($this->placeholderEnd, '~'),
        );
    }

    /**
     * Replaces {{var}} string with provided value
     */
    public function place(string $var, $val): self
    {
        $this->vars[$var] = $val;
        return $this;
    }

    /**
     * Sets all template vars
     */
    public function setVars(array $vars): void
    {
        $this->vars = $vars;
    }

    public function getVar(string $name)
    {
        return $this->vars[$name] ?? null;
    }

    /**
     * Fills up template string with placed variables.
     */
    public function produce(): string
    {
        return preg_replace_callback($this->regex, function (array $match): string {
            $placeholder = $match[1];
            $value       = $this->vars;

            foreach (explode('.', trim($placeholder, '\'"')) as $segment) {
                if (is_array($value) && array_key_exists($segment, $value)) {
                    $value = $value[$segment];
                } else {
                    return $match[0];
                }
            }
            $value = $this->encoderFunction !== null
                ? ($this->encoderFunction)($value)
                : $value;

            return is_string($value) ? $value : strval($value);
        }, $this->template);
    }
}
