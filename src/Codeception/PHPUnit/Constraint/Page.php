<?php
namespace Codeception\PHPUnit\Constraint;

class Page extends \PHPUnit_Framework_Constraint_StringContains
{

    protected function failureDescription($other)
    {
        return 'response ' . $this->toString().". Response was saved to 'log' directory";
    }

}
