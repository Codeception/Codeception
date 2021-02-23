<?php

declare(strict_types=1);

use Codeception\Event\SuiteEvent;
use Codeception\Lib\Notification;
use Codeception\Subscriber\ErrorHandler;
use Codeception\Suite;

class ErrorHandlerTest extends \Codeception\PHPUnit\TestCase
{
    /**
     * @var int|null
     */
    private $originalErrorLevel;

    public function _setUp(): void
    {
        $this->originalErrorLevel = error_reporting();
    }

    public function _tearDown(): void
    {
        // Deprecation message test changes error_level
        error_reporting($this->originalErrorLevel);
    }

    public function testDeprecationMessagesRespectErrorLevelSetting(): void
    {
        $errorHandler = new ErrorHandler();

        $suiteEvent = new SuiteEvent(new Suite(), null, ['error_level' => 'E_ERROR']);
        $errorHandler->handle($suiteEvent);

        //Satisfying The Premature Exit Handling
        $errorHandler->onFinish($suiteEvent);

        Notification::all(); //clear the messages
        $errorHandler->errorHandler(E_USER_DEPRECATED, 'deprecated message', __FILE__, (string)__LINE__, []);

        $this->assertEquals([], Notification::all(), 'Deprecation message was added to notifications');
    }

    public function testShowsLocationOfWarning(): void
    {
        $this->expectException(\PHPUnit\Framework\Exception::class);
        $SEP = DIRECTORY_SEPARATOR;
        $this->expectExceptionMessage("Undefined variable: file at tests{$SEP}unit{$SEP}Codeception{$SEP}Subscriber{$SEP}ErrorHandlerTest.php");
        trigger_error('Undefined variable: file', E_USER_WARNING);
    }
}
