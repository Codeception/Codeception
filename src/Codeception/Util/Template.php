<?php
namespace Codeception\Util;

/**
 * Basic template engine used for generating initial Cept/Cest/Test files.
 */
class Template
{
    protected $template;
    protected $vars = [];
    protected $placehodlerStart;
    protected $placeholderEnd;

    /**
     * Takes a template string
     *
     * @param $template
     */
    public function __construct($template, $placeholderStart = '{{', $placeholderEnd = '}}')
    {
        $this->template = $template;
        $this->placehodlerStart = $placeholderStart;
        $this->placeholderEnd = $placeholderEnd;
    }

    /**
     * Replaces {{var}} string with provided value
     *
     * @param $var
     * @param $val
     * @return $this
     */
    public function place($var, $val)
    {
        $this->vars[$var] = $val;
        return $this;
    }

    /**
     * Sets all template vars
     *
     * @param array $vars
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;
    }

    /**
     * Fills up template string with placed variables.
     *
     * @return mixed
     */
    public function produce()
    {
        $result = $this->template;
        $regex = sprintf('~%s([\w\.]+)%s~m', $this->placehodlerStart, $this->placeholderEnd);

        $matched = preg_match_all($regex, $result, $matches, PREG_SET_ORDER);
        if (!$matched) {
            return $result;
        }

        foreach ($matches as $match) { // fill in placeholders
            $placeholder = $match[1];
            if (!isset($this->vars[$placeholder])) {
                continue;
            }
            $result = str_replace($this->placehodlerStart . $placeholder . $this->placeholderEnd, $this->vars[$placeholder], $result);
        }
        return $result;
    }
}
