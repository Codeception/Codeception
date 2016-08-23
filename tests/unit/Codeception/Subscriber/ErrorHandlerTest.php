<?php

use Codeception\Event\SuiteEvent;
use Codeception\Lib\Notification;
use Codeception\Subscriber\ErrorHandler;
use Codeception\Suite;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{

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
