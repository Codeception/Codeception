<?php

declare(strict_types=1);

namespace Codeception;

use Closure;
use Codeception\Lib\ModuleContainer;
use Codeception\Step\Argument\FormattedOutput;
use Codeception\Step\Meta as MetaStep;
use Codeception\Util\Locator;
use Exception;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use RuntimeException;
use Stringable;

abstract class Step implements Stringable
{
    /**
     * @var int
     */
    public const DEFAULT_MAX_LENGTH = 200;
    /**
     * @var int
     */
    public const STACK_POSITION     = 3;
    public bool $executed = false;

    protected bool $failed   = false;
    protected bool $isTry    = false;
    protected string|int|null $line = null;
    protected ?string $file         = null;
    protected string $prefix        = 'I';
    protected ?MetaStep $metaStep   = null;

    /** @param string[] $arguments */
    public function __construct(protected string $action, protected array $arguments = [])
    {
    }

    public function saveTrace(): void
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
        if (count($stack) <= self::STACK_POSITION) {
            return;
        }
        $trace = $stack[self::STACK_POSITION - 1];
        if (!isset($trace['file'])) {
            return;
        }
        $this->file = $trace['file'];
        $this->line = $trace['line'];
        $this->addMetaStep($trace, $stack);
    }

    private function isTestFile(string $file): int|false
    {
        return preg_match('#[^\\' . DIRECTORY_SEPARATOR . '](Cest|Cept|Test).php$#', $file);
    }

    public function getName(): string
    {
        $parts = explode('\\', self::class);
        return end($parts);
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getFilePath(): ?string
    {
        return $this->file ? codecept_relative_path($this->file) : null;
    }

    public function getLineNumber(): ?int
    {
        return $this->line ?: null;
    }

    public function hasFailed(): bool
    {
        return $this->failed;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgumentsAsString(int $maxLength = self::DEFAULT_MAX_LENGTH): string
    {
        $arguments     = $this->arguments;
        $argumentCount = count($arguments);
        $totalLength   = $argumentCount - 1;

        foreach ($arguments as $key => $argument) {
            $stringified     = $this->stringifyArgument($argument);
            $arguments[$key] = $stringified;
            $totalLength   += mb_strlen($stringified, 'utf-8');
        }

        if ($totalLength > $maxLength && $maxLength > 0) {
            uasort($arguments, fn($a, $b): int => mb_strlen($a, 'utf-8') <=> mb_strlen($b, 'utf-8'));

            $allowedLength      = floor(($maxLength - $argumentCount + 1) / $argumentCount);
            $lengthRemaining    = $maxLength;
            $argumentsRemaining = $argumentCount;

            foreach ($arguments as $key => $arg) {
                --$argumentsRemaining;
                if (mb_strlen($arg, 'utf-8') > $allowedLength) {
                    $arguments[$key] = mb_substr($arg, 0, (int)$allowedLength - 4, 'utf-8')
                        . '...'
                        . mb_substr($arg, -1, 1, 'utf-8');
                    $lengthRemaining -= ($allowedLength + 1);
                } else {
                    $lengthRemaining -= (mb_strlen($arg, 'utf-8') + 1);
                    if ($argumentsRemaining > 0) {
                        $allowedLength = floor(($lengthRemaining - $argumentsRemaining + 1) / $argumentsRemaining);
                    }
                }
            }

            ksort($arguments);
        }

        return implode(',', $arguments);
    }

    protected function stringifyArgument(mixed $argument): string
    {
        if (is_string($argument)) {
            return '"' . strtr($argument, ["\n" => '\\n', "\r" => '\\r', "\t" => ' ']) . '"';
        }
        if (is_resource($argument)) {
            $argument = (string) $argument;
        } elseif (is_array($argument)) {
            foreach ($argument as $key => $value) {
                if (is_object($value)) {
                    $argument[$key] = $this->getClassName($value);
                }
            }
        } elseif (is_object($argument)) {
            if ($argument instanceof FormattedOutput) {
                $argument = $argument->getOutput();
            } elseif (method_exists($argument, '__toString')) {
                $argument = (string) $argument;
            } elseif ($argument::class === 'Facebook\\WebDriver\\WebDriverBy') {
                $argument = Locator::humanReadableString($argument);
            } elseif ($argument instanceof Constraint) {
                $argument = $argument->toString();
            } else {
                $argument = $this->getClassName($argument);
            }
        }
        $arg_str = json_encode(
            $argument,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
        return str_replace('"', '"', $arg_str);
    }

    protected function getClassName(object $argument): string
    {
        if ($argument instanceof Closure) {
            return Closure::class;
        }
        if ($argument instanceof MockObject) {
            $parent = get_parent_class($argument);
            if ($parent) {
                return $this->formatClassName($parent);
            }
            foreach ((new ReflectionClass($argument))->getInterfaceNames() as $interface) {
                if (!str_starts_with($interface, 'PHPUnit\\') && !str_starts_with($interface, 'Codeception\\')) {
                    return $this->formatClassName($interface);
                }
            }
        }
        return $this->formatClassName($argument::class);
    }

    protected function formatClassName(string $classname): string
    {
        return trim($classname, '\\');
    }

    public function getPhpCode(int $maxLength): string
    {
        $base      = "\${$this->prefix}->" . $this->getAction() . '(';
        $remaining = $maxLength - mb_strlen($base, 'utf-8') - 1;
        return $base . $this->getHumanizedArguments($remaining) . ')';
    }

    public function getMetaStep(): ?MetaStep
    {
        return $this->metaStep;
    }

    public function __toString(): string
    {
        return $this->humanize($this->getAction()) . ' ' . $this->getHumanizedArguments();
    }

    public function toString(int $maxLength): string
    {
        $action    = $this->humanize($this->getAction());
        $remaining = $maxLength - mb_strlen($action, 'utf-8') - 1;
        return $action . ' ' . $this->getHumanizedArguments($remaining);
    }

    public function getHtml(string $highlightColor = '#732E81'): string
    {
        if ($this->arguments === []) {
            return sprintf('%s %s', ucfirst($this->prefix), $this->humanize($this->getAction()));
        }

        return sprintf(
            '%s %s <span style="color: %s">%s</span>',
            ucfirst($this->prefix),
            htmlspecialchars($this->humanize($this->getAction()), ENT_QUOTES | ENT_SUBSTITUTE),
            $highlightColor,
            htmlspecialchars($this->getHumanizedArguments(0), ENT_QUOTES | ENT_SUBSTITUTE)
        );
    }

    public function getHumanizedActionWithoutArguments(): string
    {
        return $this->humanize($this->getAction());
    }

    public function getHumanizedArguments(int $maxLength = self::DEFAULT_MAX_LENGTH): string
    {
        return $this->getArgumentsAsString($maxLength);
    }

    protected function clean(string $text): string
    {
        return str_replace('\/', '', $text);
    }

    protected function humanize(string $text): string
    {
        $text = preg_replace('#([A-Z]+)([A-Z][a-z])#', '\\1 \\2', $text);
        $text = preg_replace('#([a-z\d])([A-Z])#', '\\1 \\2', $text);
        $text = preg_replace('#\\bdont\\b#', "don't", $text);
        return mb_strtolower($text, 'UTF-8');
    }

    /**
     * @return mixed
     */
    public function run(?ModuleContainer $container = null)
    {
        $this->executed = true;
        if (!$container instanceof ModuleContainer) {
            return null;
        }
        $module = $container->moduleForAction($this->action);
        if (!is_callable([$module, $this->action])) {
            throw new RuntimeException("Action '{$this->action}' can't be called");
        }
        try {
            return $module->{$this->action}(...$this->arguments);
        } catch (Exception $e) {
            if ($this->isTry) {
                throw $e;
            }
            $this->failed = true;
            $this->metaStep?->setFailed(true);
            throw $e;
        }
    }

    /**
     * If steps are combined into one method they can be reproduced as meta-step.
     * We are using stack trace to analyze if steps were called from test, if not - they were called from meta-step.
     */
    protected function addMetaStep(array $step, array $stack): void
    {
        if ($this->isTestFile($this->file) || $step['class'] === Scenario::class) {
            return;
        }
        for ($i = count($stack) - self::STACK_POSITION - 1; isset($stack[$i]); --$i) {
            $step = $stack[$i];
            if (!isset($step['file'], $step['function'], $step['class']) || !$this->isTestFile($step['file'])) {
                continue;
            }
            $this->metaStep = new Step\Meta(
                $step['function'],
                array_map(fn($v) => $v, array_values($step['args']))
            );
            $this->metaStep->setTraceInfo($step['file'], $step['line']);
            if (!in_array(Actor::class, class_parents($step['class']))) {
                if (isset($step['object'])) {
                    $this->metaStep->setPrefix($step['object']::class . ':');
                } else {
                    $this->metaStep->setPrefix($step['class'] . ':');
                }
            }
            return;
        }
    }

    public function setMetaStep(?MetaStep $metaStep): void
    {
        $this->metaStep = $metaStep;
    }

    public function getPrefix(): string
    {
        return $this->prefix . ' ';
    }
}
