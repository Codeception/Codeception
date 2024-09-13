<?php

declare(strict_types=1);

namespace Codeception\Util;

use function array_key_exists;
use function explode;
use function is_array;
use function preg_match_all;
use function sprintf;
use function str_replace;

/**
 * Basic template engine used for generating initial Cept/Cest/Test files.
 */
class Template
{
    private array $vars = [];

    public function __construct(
        private string $template,
        private readonly string $placeholderStart = '{{',
        private readonly string $placeholderEnd = '}}',
        private readonly ?string $encoderFunction = null,
    ) {
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
        $result = $this->template;
        $regex = sprintf('~%s([\w\.]+)%s~m', $this->placeholderStart, $this->placeholderEnd);

        $matched = preg_match_all($regex, $result, $matches, PREG_SET_ORDER);
        if (!$matched) {
            return $result;
        }

        foreach ($matches as $match) { // fill in placeholders
            $placeholder = $match[1];
            $value = $this->vars;

            foreach (explode('.', trim($placeholder, '\'"')) as $segment) {
                if (is_array($value) && array_key_exists($segment, $value)) {
                    $value = $value[$segment];
                } else {
                    continue 2;
                }
            }

            if ($this->encoderFunction !== null) {
                $value = ($this->encoderFunction)($value);
            } elseif (!is_string($value)) {
                $value = (string)$value;
            }

            $result = str_replace($this->placeholderStart . $placeholder . $this->placeholderEnd, $value, $result);
        }
        return $result;
    }
}
