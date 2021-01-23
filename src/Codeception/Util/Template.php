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
    protected $template;
    protected $vars = [];
    protected $placeholderStart;
    protected $placeholderEnd;

    /**
     * Takes a template string
     *
     * @param $template
     */
    public function __construct($template, string $placeholderStart = '{{', string $placeholderEnd = '}}')
    {
        $this->template         = $template;
        $this->placeholderStart = $placeholderStart;
        $this->placeholderEnd   = $placeholderEnd;
    }

    /**
     * Replaces {{var}} string with provided value
     */
    public function place($var, $val): self
    {
        $this->vars[$var] = $val;
        return $this;
    }

    /**
     * Sets all template vars
     *
     * @param array $vars
     */
    public function setVars(array $vars): void
    {
        $this->vars = $vars;
    }

    /**
     * @return mixed|void
     */
    public function getVar($name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        }
    }

    /**
     * Fills up template string with placed variables.
     *
     * @return mixed
     */
    public function produce()
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
            foreach (explode('.', $placeholder) as $segment) {
                if (is_array($value) && array_key_exists($segment, $value)) {
                    $value = $value[$segment];
                } else {
                    continue 2;
                }
            }

            $result = str_replace($this->placeholderStart . $placeholder . $this->placeholderEnd, $value, $result);
        }
        return $result;
    }
}
