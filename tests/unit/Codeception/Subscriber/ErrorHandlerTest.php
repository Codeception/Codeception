<?php

use Codeception\Event\SuiteEvent;
use Codeception\Lib\Notification;
use Codeception\Subscriber\ErrorHandler;
use Codeception\Suite;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testErrorLevelSettingOverridesCurrentErrorReportingLevel()
    {
        $this->setExpectedException('PHPUnit_Framework_Exception', 'error message');

        $errorHandler = new ErrorHandler();
        $suiteEvent = new SuiteEvent(new Suite(), null, ['error_level' => 'E_ERROR']);
        $errorHandler->handle($suiteEvent);

        $oldLevel = error_reporting(0);
        $errorHandler->errorHandler(E_ERROR, 'error message', __FILE__, __LINE__, []);
        error_reporting($oldLevel);
    }

    public function testDeprecationMessagesRespectErrorLevelSetting()
    {
        $errorHandler = new ErrorHandler();

        $suiteEvent = new SuiteEvent(new Suite(), null, ['error_level' => 'E_ERROR']);
        $errorHandler->handle($suiteEvent);

        $messagesBeforeRun = count(Notification::all());
        $errorHandler->errorHandler(E_USER_DEPRECATED, 'deprecated message', __FILE__, __LINE__, []);
        $messagesAfterRun = count(Notification::all());
        $this->assertSame($messagesBeforeRun, $messagesAfterRun, 'Deprecation message was added to notifications');
    }
}
