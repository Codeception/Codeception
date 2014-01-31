<?php

use Codeception\Event\SuiteEvent;

class CodeCoverageTest extends PHPUnit_Framework_TestCase
{
    public function testThatAfterSuiteRespectsRemoteSetting()
    {
        $reflection = new ReflectionClass('Codeception\Subscriber\CodeCoverage');
        $coverageProperty = $reflection->getProperty('coverage');
        $coverageProperty->setAccessible(true);
        $enabledProperty = $reflection->getProperty('enabled');
        $enabledProperty->setAccessible(true);
        $remoteProperty = $reflection->getProperty('remote');
        $remoteProperty->setAccessible(true);

        /** @var $phpunitCodeCoverageMock \PHP_CodeCoverage|PHPUnit_Framework_MockObject_MockObject */
        $phpunitCodeCoverageMock = $this
            ->getMockBuilder('PHP_CodeCoverage')
            ->disableOriginalConstructor()
            ->setMethods(array('merge'))
            ->getMock();
        $phpunitCodeCoverageMock
            ->expects($this->once())
            ->method('merge');

        /** @var $codeCoverageMock \Codeception\Subscriber\CodeCoverage|PHPUnit_Framework_MockObject_MockObject */
        $codeCoverageMock = $this
            ->getMockBuilder('Codeception\Subscriber\CodeCoverage')
            ->disableOriginalConstructor()
            ->setMethods(array('getRemoteConnectionModule'))
            ->getMock();

        $coverageProperty->setValue($codeCoverageMock, $phpunitCodeCoverageMock);
        $enabledProperty->setValue($codeCoverageMock, true);
        $remoteProperty->setValue($codeCoverageMock, false);

        /** @var $testSuite PHPUnit_Framework_TestSuite|PHPUnit_Framework_MockObject_MockObject */
        $testSuite  = $this->getMock('PHPUnit_Framework_TestSuite', array(), array(), '', false);
        $testResult = new PHPUnit_Framework_TestResult;
        $testResult->setCodeCoverage(new \PHP_CodeCoverage());
        $suiteEvent = new SuiteEvent($testSuite, $testResult);

        $codeCoverageMock->afterSuite($suiteEvent);
    }
}
