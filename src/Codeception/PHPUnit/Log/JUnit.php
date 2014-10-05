<?php
namespace Codeception\PHPUnit\Log;

use Codeception\Configuration;
use Codeception\TestCase\Interfaces\Reported;

class JUnit extends \PHPUnit_Util_Log_JUnit
{
    protected $strictAttributes = ['file', 'name', 'class'];

    public function startTest(\PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof Reported) {
            return parent::startTest($test);
        }

        $this->currentTestCase = $this->document->createElement('testcase');

        $isStrict = Configuration::config()['settings']['strict_xml'];

        foreach ($test->getReportFields() as $attr => $value) {
            if ($isStrict and !in_array($attr, $this->strictAttributes)) {
                continue;
            }
            $this->currentTestCase->setAttribute($attr, $value);
        }
    }

}
