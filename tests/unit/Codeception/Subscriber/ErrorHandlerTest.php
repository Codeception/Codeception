<?php

use Codeception\Event\SuiteEvent;
use Codeception\Lib\Notification;
use Codeception\Subscriber\ErrorHandler;
use Codeception\Suite;

class ErrorHandlerTest extends \Codeception\PHPUnit\TestCase
{
    private $originalErrorLevel;

    public function _setUp()
    {
        $this->originalErrorLevel = error_reporting();
    }

    public function _tearDown()
    {
        // Deprecation message test changes error_level
        error_reporting($this->originalErrorLevel);
    }

    public function testDeprecationMessagesRespectErrorLevelSetting()
    {
        $errorHandler = new ErrorHandler();

        $suiteEvent = new SuiteEvent(new Suite(), null, ['error_level' => 'E_ERROR']);
        $errorHandler->handle($suiteEvent);

        //Satisfying The Premature Exit Handling
        $errorHandler->onFinish($suiteEvent);

        Notification::all(); //clear the messages
        $errorHandler->errorHandler(E_USER_DEPRECATED, 'deprecated message', __FILE__, __LINE__, []);

        $this->assertEquals([], Notification::all(), 'Deprecation message was added to notifications');
    }

    public function testShowsLocationOfWarning()
    {
        if (PHP_MAJOR_VERSION === 5) {
            $this->expectException(\PHPUnit_Framework_Exception::class);
        } else {
            $this->expectException(\PHPUnit\Framework\Exception::class);
        }
        $SEP = DIRECTORY_SEPARATOR;
        $this->expectExceptionMessage("Undefined variable: file at tests{$SEP}unit{$SEP}Codeception{$SEP}Subscriber{$SEP}ErrorHandlerTest.php:48");
        trigger_error('Undefined variable: file', E_USER_WARNING);
    }
}
