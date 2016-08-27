<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Descriptor;

trait CodeCoverage
{
    /**
     * @return \PHPUnit_Framework_TestResult
     */
    abstract public function getTestResultObject();

    public function codeCoverageStart()
    {
        $codeCoverage = $this->getTestResultObject()->getCodeCoverage();
        if (!$codeCoverage) {
            return;
        }
        $codeCoverage->start(Descriptor::getTestSignature($this));
    }

    public function codeCoverageEnd($status, $time)
    {
        $codeCoverage = $this->getTestResultObject()->getCodeCoverage();
        if (!$codeCoverage) {
            return;
        }

        try {
            $codeCoverage->stop(true);
        } catch (\PHP_CodeCoverage_Exception $cce) {
            if ($status === \Codeception\Test\Test::STATUS_OK) {
                $this->getTestResultObject()->addError($this, $cce, $time);
            }
        }
    }
}
