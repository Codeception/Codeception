<?php

declare(strict_types=1);

namespace Codeception;

use Closure;
use Codeception\Lib\ModuleContainer;
use Codeception\Step\Argument\FormattedOutput;
use Codeception\Step\Meta as MetaStep;
use Codeception\Util\Locator;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
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
    public const STACK_POSITION = 3;

    public bool $executed = false;

    protected string|int|null $line = null;

    protected ?string $file = null;

    protected string $prefix = 'I';

    protected ?MetaStep $metaStep = null;

    protected bool $failed = false;

    protected bool $isTry = false;

    public function __construct(protected string $action, protected array $arguments = [])
    {
    }

    public function saveTrace(): void
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);

        if (count($stack) <= self::STACK_POSITION) {
            return;
        }

        $traceLine = $stack[self::STACK_POSITION - 1];

        if (!isset($traceLine['file'])) {
            return;
        }
        $this->file = $traceLine['file'];
        $this->line = $traceLine['line'];

        $this->addMetaStep($traceLine, $stack);
    }

    private function isTestFile(string $file)
    {
        return preg_match('#[^\\' . DIRECTORY_SEPARATOR . '](Cest|Cept|Test).php$#', $file);
    }

    public function getName(): string
    {
        $class = explode('\\', __CLASS__);
        return end($class);
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getFilePath(): ?string
    {
        if ($this->file) {
            return codecept_relative_path($this->file);
        }
        return null;
    }

    public function getLineNumber(): ?int
    {
        if ($this->line) {
            return $this->line;
        }
        return null;
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
        $arguments = $this->arguments;

        $argumentCount = count($arguments);
        $totalLength = $argumentCount - 1; // count separators before adding length of individual arguments

        foreach ($arguments as $key => $argument) {
            $stringifiedArgument = $this->stringifyArgument($argument);
            $arguments[$key] = $stringifiedArgument;
            $totalLength += mb_strlen($stringifiedArgument, 'utf-8');
        }

        if ($totalLength > $maxLength && $maxLength > 0) {
            //sort arguments from shortest to longest
            uasort($arguments, function ($arg1, $arg2): int {
                $length1 = mb_strlen($arg1, 'utf-8');
                $length2 = mb_strlen($arg2, 'utf-8');
                if ($length1 === $length2) {
                    return 0;
                }
                return ($length1 < $length2) ? -1 : 1;
            });

            $allowedLength = floor(($maxLength - $argumentCount + 1) / $argumentCount);

            $lengthRemaining = $maxLength;
            $argumentsRemaining = $argumentCount;
            foreach ($arguments as $key => $argument) {
                --$argumentsRemaining;
                if (mb_strlen($argument, 'utf-8') > $allowedLength) {
                    $arguments[$key] = mb_substr($argument, 0, (int)$allowedLength - 4, 'utf-8') . '...' . mb_substr($argument, -1, 1, 'utf-8');
                    $lengthRemaining -= ($allowedLength + 1);
                } else {
                    $lengthRemaining -= (mb_strlen($argument, 'utf-8') + 1);
                    //recalculate allowed length because this argument was short
                    if ($argumentsRemaining > 0) {
                        $allowedLength = floor(($lengthRemaining - $argumentsRemaining + 1) / $argumentsRemaining);
                    }
                }
            }

            //restore original order of arguments
            ksort($arguments);
        }

        return implode(',', $arguments);
    }

    protected function stringifyArgument(mixed $argument): string
    {
        if (is_string($argument)) {
            return '"' . strtr($argument, ["\n" => '\n', "\r" => '\r', "\t" => ' ']) . '"';
        } elseif (is_resource($argument)) {
            $argument = (string)$argument;
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
                $argument = (string)$argument;
            } elseif ($argument::class == 'Facebook\WebDriver\WebDriverBy') {
                $argument = Locator::humanReadableString($argument);
            } else {
                $argument = $this->getClassName($argument);
            }
        }
        $arg_str = json_encode($argument, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        return str_replace('\"', '"', $arg_str);
    }

    protected function getClassName(object $argument): string
    {
        if ($argument instanceof Closure) {
            return Closure::class;
        } elseif ($argument instanceof MockObject) {
            $parentClass = get_parent_class($argument);
            $reflection = new \ReflectionClass($argument);

            if ($parentClass !== false) {
                return $this->formatClassName($parentClass);
            }

            $interfaces = $reflection->getInterfaceNames();
            foreach ($interfaces as $interface) {
                if (str_starts_with($interface, 'PHPUnit\\')) {
                    continue;
                }
                if (str_starts_with($interface, 'Codeception\\')) {
                    continue;
                }
                return $this->formatClassName($interface);
            }
        }

        return $this->formatClassName($argument::class);
    }

    protected function formatClassName(string $classname): string
    {
        return trim($classname, "\\");
    }

    public function getPhpCode(int $maxLength): string
    {
        $result = "\${$this->prefix}->" . $this->getAction() . '(';
        $maxLength = $maxLength - mb_strlen($result, 'utf-8') - 1;
        return $result . ($this->getHumanizedArguments($maxLength) . ')');
    }

    public function getMetaStep(): ?MetaStep
    {
        return $this->metaStep;
    }

    public function __toString(): string
    {
        $humanizedAction = $this->humanize($this->getAction());
        return $humanizedAction . ' ' . $this->getHumanizedArguments();
    }

    public function toString(int $maxLength): string
    {
        $humanizedAction = $this->humanize($this->getAction());
        $maxLength = $maxLength - mb_strlen($humanizedAction, 'utf-8') - 1;
        return $humanizedAction . ' ' . $this->getHumanizedArguments($maxLength);
    }

    public function getHtml(string $highlightColor = '#732E81'): string
    {
        if (empty($this->arguments)) {
            return sprintf('%s %s', ucfirst($this->prefix), $this->humanize($this->getAction()));
        }

        return sprintf('%s %s <span style="color: %s">%s</span>', ucfirst($this->prefix), htmlspecialchars($this->humanize($this->getAction()), ENT_QUOTES | ENT_SUBSTITUTE), $highlightColor, htmlspecialchars($this->getHumanizedArguments(0), ENT_QUOTES | ENT_SUBSTITUTE));
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
        $text = preg_replace('#\bdont\b#', "don't", $text);
        return mb_strtolower($text, 'UTF-8');
    }

    /**
     * @return mixed
     */
    public function run(ModuleContainer $container = null)
    {
        $this->executed = true;
        if ($container === null) {
            return null;
        }
        $activeModule = $container->moduleForAction($this->action);

        if (!is_callable([$activeModule, $this->action])) {
            throw new RuntimeException("Action '{$this->action}' can't be called");
        }

        try {
            $res = call_user_func_array([$activeModule, $this->action], $this->arguments);
        } catch (Exception $e) {
            if ($this->isTry) {
                throw $e;
            }
            $this->failed = true;
            $this->getMetaStep()?->setFailed(true);
            throw $e;
        }

        return $res;
    }

    /**
     * If steps are combined into one method they can be reproduced as meta-step.
     * We are using stack trace to analyze if steps were called from test, if not - they were called from meta-step.
     */
    protected function addMetaStep(array $step, array $stack): void
    {
        if (($this->isTestFile($this->file)) || ($step['class'] == Scenario::class)) {
            return;
        }

        $i = count($stack) - self::STACK_POSITION - 1;

        // get into test file and retrieve its actual call
        while (isset($stack[$i])) {
            $step = $stack[$i];
            --$i;
            if (!isset($step['file']) || !isset($step['function']) || !isset($step['class'])) {
                continue;
            }

            if (!$this->isTestFile($step['file'])) {
                continue;
            }

            // in case arguments were passed by reference, copy args array to ensure dereference.  array_values() does not dereference values
            $this->metaStep = new Step\Meta($step['function'], array_map(fn ($i) => $i, array_values($step['args'])));
            $this->metaStep->setTraceInfo($step['file'], $step['line']);

            // page objects or other classes should not be included with "I"
            if (!in_array(Actor::class, class_parents($step['class']))) {
                if (isset($step['object'])) {
                    $this->metaStep->setPrefix($step['object']::class . ':');
                    return;
                }

                $this->metaStep->setPrefix($step['class'] . ':');
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
