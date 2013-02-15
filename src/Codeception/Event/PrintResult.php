<?php

namespace Codeception\Event;
use \Symfony\Component\EventDispatcher\Event;

class PrintResult extends Event
{
    /**
     * @var \PHPUnit_Framework_TestResult
     */
    protected $result;

    /**
     * @var \PHPUnit_TextUI_ResultPrinter
     */
    protected $printer;

    function __construct(\PHPUnit_Framework_TestResult $result, \PHPUnit_TextUI_ResultPrinter $printer)
    {
        $this->result = $result;
        $this->printer = $printer;
    }

    /**
     * @return \PHPUnit_TextUI_ResultPrinter
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
