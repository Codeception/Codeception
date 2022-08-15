<?php

declare(strict_types=1);

use Codeception\Event\SuiteEvent;
use Codeception\Exception\Warning;
use Codeception\Lib\Notification;
use Codeception\Subscriber\ErrorHandler;
use Codeception\Suite;
use PHPUnit\Runner\Version;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ErrorHandlerTest extends \Codeception\PHPUnit\TestCase
{
    /**
     * @var int|null
     */
    private ?int $originalErrorLevel = null;

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

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $suiteEvent = new SuiteEvent(new Suite($eventDispatcher), ['error_level' => 'E_ERROR']);
        $errorHandler->handle($suiteEvent);

        //Satisfying The Premature Exit Handling
        $errorHandler->onFinish($suiteEvent);

        Notification::all(); //clear the messages
        $errorHandler->errorHandler(E_USER_DEPRECATED, 'deprecated message', __FILE__, __LINE__, []);

        $this->assertSame([], Notification::all(), 'Deprecation message was added to notifications');
    }

    public function testShowsLocationOfWarning()
    {
        if (Version::series() < 10) {
            $this->expectWarning();
            $this->expectWarningMessage('Undefined variable: file');
        } else {
            $this->expectException(Warning::class);
            $this->expectExceptionMessageMatches('/Undefined variable: file at ' . preg_quote(__FILE__, '/') . ':58/');
        }
        trigger_error('Undefined variable: file', E_USER_WARNING);
    }
}
