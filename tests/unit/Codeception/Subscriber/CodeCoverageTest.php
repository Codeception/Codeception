<?php

use Codeception\Event\Suite;

class CodeCoverageTest extends PHPUnit_Framework_TestCase
{
    public function testAfterSuiteRespectsRemoteSetting()
    {
        /** @var $codeCoverageMock \Codeception\Subscriber\CodeCoverage|PHPUnit_Framework_MockObject_MockObject */
        $codeCoverageMock = $this
            ->getMockBuilder('Codeception\Subscriber\CodeCoverage')
            ->disableOriginalConstructor()
            ->setMethods(array('getRemoteConnectionModule'))
            ->getMock();

        $reflection = new ReflectionClass('Codeception\Subscriber\CodeCoverage');
        $enabledReflection = $reflection->getProperty('enabled');
        $enabledReflection->setAccessible(true);
        $enabledReflection->setValue($codeCoverageMock, true);
        $remoteReflection = $reflection->getProperty('remote');
        $remoteReflection->setAccessible(true);
        $remoteReflection->setValue($codeCoverageMock, false);

        $codeCoverageMock->expects($this->never())
            ->method('getRemoteConnectionModule');

        /** @var $testSuite PHPUnit_Framework_TestSuite|PHPUnit_Framework_MockObject_MockObject */
        $testSuite  = $this->getMock('PHPUnit_Framework_TestSuite', array(), array(), '', false);
        $testResult = new PHPUnit_Framework_TestResult;
        $suiteEvent = new Suite($testSuite, $testResult);

        $codeCoverageMock->afterSuite($suiteEvent);
    }
}
