<?php declare(strict_types=1);

namespace Codeception\ResultPrinter\Subscriber;

use Codeception\ResultPrinter;

abstract class Subscriber
{
    private ResultPrinter $printer;

    public function __construct(ResultPrinter $printer)
    {
        $this->printer = $printer;
    }

    protected function printer(): ResultPrinter
    {
        return $this->printer;
    }
}
