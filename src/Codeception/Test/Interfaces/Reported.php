<?php
namespace Codeception\Test\Interfaces;

interface Reported
{
    /**
     * Field values for XML/JSON/TAP reports
     */
    public function getReportFields(): array;
}
