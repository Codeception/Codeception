<?php

use Codeception\Event\Suite;

class RemoteCodeCoverageTest extends PHPUnit_Framework_TestCase
{
    public function testThatAfterSuiteRespectsRemoteFalseSetting()
    {
        $reflection = new ReflectionClass('Codeception\Subscriber\RemoteCodeCoverage');
        $settingsProperty = $reflection->getProperty('settings');
        $settingsProperty->setAccessible(true);

        /** @var $codeCoverageMock \Codeception\Subscriber\RemoteCodeCoverage|PHPUnit_Framework_MockObject_MockObject */
        $codeCoverageMock = $this
            ->getMockBuilder('Codeception\Subscriber\RemoteCodeCoverage')
            ->disableOriginalConstructor()
            ->setMethods(array('getRemoteConnectionModule'))
            ->getMock();
        $codeCoverageMock
            ->expects($this->never())
            ->method('getRemoteConnectionModule');

        $settingsProperty->setValue($codeCoverageMock, array('enabled' => true, 'remote' => false));

        /** @var $testSuite PHPUnit_Framework_TestSuite|PHPUnit_Framework_MockObject_MockObject */
        $testSuite  = $this->getMock('PHPUnit_Framework_TestSuite', array(), array(), '', false);
        $testResult = new PHPUnit_Framework_TestResult;
        $testResult->setCodeCoverage(new \PHP_CodeCoverage());
        $suiteEvent = new Suite($testSuite, $testResult);

        $codeCoverageMock->beforeSuite($suiteEvent);
    }
}
