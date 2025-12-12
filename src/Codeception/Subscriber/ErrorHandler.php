<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\Deprecation;
use Codeception\Exception\Error;
use Codeception\Exception\Notice;
use Codeception\Exception\Warning;
use Codeception\Lib\Notification;
use PHPUnit\Runner\Version as PHPUnitVersion;
use Symfony\Bridge\PhpUnit\DeprecationErrorHandler as SymfonyDeprecationErrorHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function call_user_func;
use function class_exists;
use function error_get_last;
use function error_reporting;
use function getenv;
use function in_array;
use function is_array;
use function register_shutdown_function;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;

use const PHP_VERSION_ID;

class ErrorHandler implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::SUITE_BEFORE => 'handle',
        Events::SUITE_AFTER  => 'onFinish'
    ];

    /**
     * @var bool $stopped to keep shutdownHandler from possible looping.
     */
    private bool $stopped = false;

    /**
     * @var bool $initialized to avoid double error handler substitution
     */
    private bool $initialized = false;

    private bool $deprecationsInstalled = false;

    /**
     * @var callable|null
     */
    private $oldHandler;

    private bool $suiteFinished = false;

    /**
     * @var int Stores bitmask for errors
     */
    private int $errorLevel;

    private bool $convertDeprecationsToExceptions = false;

    public function __construct()
    {
        // E_STRICT is deprecated in PHP 8.4
        $this->errorLevel = PHP_VERSION_ID < 80400 ? E_ALL & ~E_STRICT & ~E_DEPRECATED : E_ALL & ~E_DEPRECATED;
    }

    public function onFinish(SuiteEvent $event): void
    {
        $this->suiteFinished = true;
    }

    public function handle(SuiteEvent $event): void
    {
        $settings = $event->getSettings();
        if ($settings['error_level']) {
            $this->errorLevel = eval("return {$settings['error_level']};");
        }
        error_reporting($this->errorLevel);

        if ($settings['convert_deprecations_to_exceptions']) {
            $this->convertDeprecationsToExceptions = true;
        }

        if ($this->initialized) {
            return;
        }
        // We must register shutdown function before deprecation error handler to restore previous error handler
        // and silence DeprecationErrorHandler yelling about 'THE ERROR HANDLER HAS CHANGED!'
        register_shutdown_function([$this, 'shutdownHandler']);
        $this->registerDeprecationErrorHandler();
        $this->oldHandler = set_error_handler($this->errorHandler(...));
        $this->initialized = true;
    }

    public function errorHandler(int $errNum, string $errMsg, string $errFile, int $errLine, array $context = []): bool
    {
        if ((E_USER_DEPRECATED === $errNum || E_DEPRECATED === $errNum) && !$this->convertDeprecationsToExceptions) {
            $this->handleDeprecationError($errNum, $errMsg, $errFile, $errLine, $context);
            return true;
        }

        if ((error_reporting() & $errNum) === 0) {
            // This error code is not included in error_reporting
            return false;
        }

        if (str_contains($errMsg, 'Cannot modify header information')) {
            return false;
        }

        if (version_compare(PHPUnitVersion::series(), '10.0', '<')) {
            $map = [
                E_DEPRECATED        => 'PHPUnit\Framework\Error\Deprecated',
                E_USER_DEPRECATED   => 'PHPUnit\Framework\Error\Deprecated',
                E_NOTICE            => 'PHPUnit\Framework\Error\Notice',
                E_USER_NOTICE       => 'PHPUnit\Framework\Error\Notice',
                E_WARNING           => 'PHPUnit\Framework\Error\Warning',
                E_USER_WARNING      => 'PHPUnit\Framework\Error\Warning',
            ];
            $className = $map[$errNum] ?? 'PHPUnit\Framework\Error\Error';

            if (class_exists($className)) {
                throw new $className($errMsg, $errNum, $errFile, $errLine);
            }
        }

        $errMsgWithLocation = $errMsg . ' at ' . $errFile . ':' . $errLine;
        throw match ($errNum) {
            E_DEPRECATED, E_USER_DEPRECATED  => new Deprecation($errMsgWithLocation, $errNum, $errFile, $errLine),
            E_NOTICE,     E_USER_NOTICE      => new Notice($errMsgWithLocation, $errNum, $errFile, $errLine),
            E_WARNING,    E_USER_WARNING     => new Warning($errMsgWithLocation, $errNum, $errFile, $errLine),
            default                          => new Error($errMsgWithLocation, $errNum, $errFile, $errLine),
        };
    }

    public function shutdownHandler(): void
    {
        if ($this->deprecationsInstalled) {
            restore_error_handler();
        }

        if ($this->stopped) {
            return;
        }
        $this->stopped = true;
        $error = error_get_last();

        if (
            !$this->suiteFinished && (
                $error === null || !in_array($error['type'], [E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR])
            )
        ) {
            echo "\n\n\nCOMMAND DID NOT FINISH PROPERLY.\n";
            exit(125);
        }
        if (!is_array($error)) {
            return;
        }
        if (error_reporting() === 0) {
            return;
        }
        // not fatal
        if (!in_array($error['type'], [E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR])) {
            return;
        }

        echo "\n\n\nFATAL ERROR. TESTS NOT FINISHED.\n";
        echo sprintf("%s \nin %s:%d\n", $error['message'], $error['file'], $error['line']);
    }

    private function registerDeprecationErrorHandler(): void
    {
        if (class_exists('\Symfony\Bridge\PhpUnit\DeprecationErrorHandler') && 'disabled' !== getenv('SYMFONY_DEPRECATIONS_HELPER')) {
            // DeprecationErrorHandler only will be installed if array('PHPUnit\Util\ErrorHandler', 'handleError')
            // is installed or no other error handlers are installed.
            // So we will remove Symfony\Component\ErrorHandler\ErrorHandler if it's installed.
            $old = set_error_handler('var_dump');
            restore_error_handler();

            if (
                $old
                && is_array($old)
                && $old !== []
                && $old[0] instanceof \Symfony\Component\ErrorHandler\ErrorHandler
            ) {
                restore_error_handler();
            }

            $this->deprecationsInstalled = true;
            SymfonyDeprecationErrorHandler::register(getenv('SYMFONY_DEPRECATIONS_HELPER'));
        }
    }

    private function handleDeprecationError(int $type, string $message, string $file, int $line, array $context): void
    {
        if (($this->errorLevel & $type) === 0) {
            return;
        }
        if ($this->deprecationsInstalled && $this->oldHandler) {
            call_user_func($this->oldHandler, $type, $message, $file, $line, $context);
            return;
        }
        Notification::deprecate("{$message}", "{$file}:{$line}");
    }
}
