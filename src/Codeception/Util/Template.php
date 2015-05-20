<?php
namespace Codeception\Util;

/**
 * Basic template engine used for generating initial Cept/Cest/Test files.
 */
class Template
{
    protected $template;
    protected $vars = [];

    /**
     * Takes a template string
     *
     * @param $template
     */
    public function __construct($template)
    {
        $this->template = $template;
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
     * Fills up template string with placed variables.
     *
     * @return mixed
     */
    public function produce()
    {
        $result = $this->template;
        foreach ($this->vars as $var => $value) {
            $result = str_replace('{{'.$var.'}}', $value, $result);
        }
        return $result;
    }


} 