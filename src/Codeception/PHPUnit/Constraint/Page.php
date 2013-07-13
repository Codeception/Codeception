<?php
namespace Codeception\PHPUnit\Constraint;

class Page extends \PHPUnit_Framework_Constraint_StringContains
{

    protected function failureDescription($other)
    {
        $page = substr($other,0,500);
        if (strlen($other) > 500) $page .= "\n[Content too long to display. See complete response in (('_log')) directory]";
        return "page -->\n$page\n--> " . $this->toString();
    }

}
