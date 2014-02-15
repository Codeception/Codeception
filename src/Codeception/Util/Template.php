<?php
namespace Codeception\Util;

class Template
{
    protected $template;
    protected $vars = [];

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function place($var, $val)
    {
        $this->vars[$var] = $val;
        return $this;
    }

    public function produce()
    {
        $result = $this->template;
        foreach ($this->vars as $var => $value) {
            $result = str_replace('{{'.$var.'}}', $value, $result);
        }
        return $result;
    }


} 