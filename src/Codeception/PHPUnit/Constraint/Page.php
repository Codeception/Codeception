<?php
namespace Codeception\PHPUnit\Constraint;

class Page extends \PHPUnit_Framework_Constraint_StringContains
{
    protected $uri;

    public function __construct($string, $uri = '')
    {
        $this->string     = (string)$string;
        $this->uri = $uri;
        $this->ignoreCase = true;
    }

    protected function failureDescription($other)
    {
        $page = substr($other,0,300);
        if (strlen($other) > 300) $page .= "\n[Content too long to display. See complete response in (('_log')) directory]";
        return "\n--> $page\n--> " . $this->toString();
    }

}
