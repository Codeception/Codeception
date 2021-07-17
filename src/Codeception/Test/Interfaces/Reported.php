<?php

namespace Codeception\Test\Interfaces;

interface Reported
{
    /**
     * Field values for XML reports
     */
    public function getReportFields(): array;
}
