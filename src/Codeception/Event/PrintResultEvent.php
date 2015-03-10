<?php

namespace Codeception\Event;

use Symfony\Component\EventDispatcher\Event;

class PrintResultEvent extends Event
{
    /**
     * @var \PHPUnit_Framework_TestResult
     */
    protected $result;

    /**
     * @var \PHPUnit_Util_Printer
     */
    protected $printer;

    public function __construct(\PHPUnit_Framework_TestResult $result, \PHPUnit_Util_Printer $printer)
    {
        $this->result = $result;
        $this->printer = $printer;
    }

    /**
     * @return \PHPUnit_Util_Printer
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * @return \PHPUnit_Framework_TestResult
     */
    public function getResult()
    {
        return $this->result;
    }
}
