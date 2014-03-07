<?php
namespace Codeception\PHPUnit\Log;

use Codeception\TestCase\Interfaces\Reported;

class JUnit extends \PHPUnit_Util_Log_JUnit
{
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof Reported) {
            return parent::startTest($test);
        }

        $this->currentTestCase = $this->document->createElement('testcase');

        foreach ($test->getReportFields() as $attr => $value) {
            $this->currentTestCase->setAttribute($attr, $value);
        }
    }

}
