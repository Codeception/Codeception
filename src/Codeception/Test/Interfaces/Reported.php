<?php
namespace Codeception\Test\Interfaces;

interface Reported
{
    /**
     * Field values for XML/JSON/TAP reports
     *
     * @return array
     */
    public function getReportFields();
}
