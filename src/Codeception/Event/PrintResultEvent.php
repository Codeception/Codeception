<?php

declare(strict_types=1);

namespace Codeception\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PrintResultEvent extends Event
{
    /**
     * @var \PHPUnit\Framework\TestResult
     */
    protected $result;

    /**
     * @var \PHPUnit\Util\Printer
     */
    protected $printer;

    public function __construct(\PHPUnit\Framework\TestResult $result, \PHPUnit\Util\Printer $printer)
    {
        $this->result = $result;
        $this->printer = $printer;
    }

    /**
     * @return \PHPUnit\Util\Printer
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * @return \PHPUnit\Framework\TestResult
     */
    public function getResult()
    {
        return $this->result;
    }
}
