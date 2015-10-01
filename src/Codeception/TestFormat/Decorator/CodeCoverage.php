<?php
namespace Codeception\TestFormat\Decorator;

trait CodeCoverage
{
    /**
     * @return \PHPUnit_Framework_TestResult
     */
    abstract public function getTestResult();

    public function codeCoverageStart()
    {
        $codeCoverage = $this->getTestResult()->getCodeCoverage();
        if (!$codeCoverage) {
            return;
        }
        $codeCoverage->start($this);
    }

    public function codeCoverageEnd(&$status, $time)
    {
        $codeCoverage = $this->getTestResult()->getCodeCoverage();
        if (!$codeCoverage) {
            return;
        }
        $linesToBeCovered = \PHPUnit_Util_Test::getLinesToBeCovered(get_class($this->testClassInstance), 'test');
        $linesToBeUsed = \PHPUnit_Util_Test::getLinesToBeUsed(get_class($this->testClassInstance), 'test');

//        try {
            $codeCoverage->stop(true, $linesToBeCovered, $linesToBeUsed);
        // should this be included???
//        } catch (\PHP_CodeCoverage_Exception_UnintentionallyCoveredCode $cce) {
//            $this->getTestResult()->addFailure(
//                $this,
//                new \PHPUnit_Framework_UnintentionallyCoveredCodeError(
//                    'This test executed code that is not listed as code to be covered or used:' .
//                    PHP_EOL . $cce->getMessage()
//                ),
//                $time
//            );
//        } catch (\PHPUnit_Framework_InvalidCoversTargetException $cce) {
//            $this->addFailure(
//                $test,
//                new PHPUnit_Framework_InvalidCoversTargetError(
//                    $cce->getMessage()
//                ),
//                $time
//            );
//        } catch (PHP_CodeCoverage_Exception $cce) {
//            $status = \Codeception\Test::STATUS_ERROR;
//
//            if (!isset($e)) {
//                $e = $cce;
//            }
//        }

    }
    

}