<?php
namespace Codeception;

use Codeception\Lib\ModuleContainer;
use Codeception\Step\Argument\FormattedOutput;
use Codeception\Step\Meta as MetaStep;
use Codeception\Util\Locator;
use PHPUnit\Framework\MockObject\MockObject;

abstract class Step
{
    const DEFAULT_MAX_LENGTH = 200;

    const STACK_POSITION = 3;
    /**
     * @var    string
     */
    protected $action;

    /**
     * @var    array
     */
    protected $arguments;

    protected $debugOutput;

    public $executed = false;

    protected $line = null;
    protected $file = null;
    protected $prefix = 'I';

    /**
     * @var MetaStep
     */
    protected $metaStep = null;

    protected $failed = false;
    protected $isTry = false;

    public function __construct($action, array $arguments = [])
    {
        $this->action = $action;
        $this->arguments = $arguments;
    }

    public function saveTrace()
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

    private function isTestFile($file)
    {
        return preg_match('~[^\\'.DIRECTORY_SEPARATOR.'](Cest|Cept|Test).php$~', $file);
    }

    public function getName()
    {
        $class = explode('\\', __CLASS__);
        return end($class);
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * @deprecated To be removed in Codeception 5.0
     */
    public function getLine()
    {
        if ($this->line && $this->file) {
            return codecept_relative_path($this->file) . ':' . $this->line;
        }
    }

    public function getFilePath()
    {
        if ($this->file) {
            return codecept_relative_path($this->file);
        }
    }

    public function getLineNumber()
    {
        if ($this->line) {
            return $this->line;
        }
    }

    public function hasFailed()
    {
        return $this->failed;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getArgumentsAsString($maxLength = self::DEFAULT_MAX_LENGTH)
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
            uasort($arguments, function ($arg1, $arg2) {
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
                $argumentsRemaining--;
                if (mb_strlen($argument, 'utf-8') > $allowedLength) {
                    $arguments[$key] = mb_substr($argument, 0, $allowedLength - 4, 'utf-8') . '...' . mb_substr($argument, -1, 1, 'utf-8');
                    $lengthRemaining -= ($allowedLength + 1);
                } else {
                    $lengthRemaining -= (mb_strlen($arguments[$key], 'utf-8') + 1);
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

    protected function stringifyArgument($argument)
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
            } elseif (get_class($argument) == 'Facebook\WebDriver\WebDriverBy') {
                $argument = Locator::humanReadableString($argument);
            } else {
                $argument = $this->getClassName($argument);
            }
        }
        $arg_str = json_encode($argument, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $arg_str = str_replace('\"', '"', $arg_str);
        return $arg_str;
    }

    protected function getClassName($argument)
    {
        if ($argument instanceof \Closure) {
            return 'Closure';
        } elseif ($argument instanceof MockObject && isset($argument->__mocked)) {
            return $this->formatClassName($argument->__mocked);
        }

        return $this->formatClassName(get_class($argument));
    }

    protected function formatClassName($classname)
    {
        return trim($classname, "\\");
    }

    public function getPhpCode($maxLength)
    {
        $result = "\${$this->prefix}->" . $this->getAction() . '(';
        $maxLength = $maxLength - mb_strlen($result, 'utf-8') - 1;

        $result .= $this->getHumanizedArguments($maxLength) .')';
        return $result;
    }

    /**
     * @return MetaStep
     */
    public function getMetaStep()
    {
        return $this->metaStep;
    }

    public function __toString()
    {
        $humanizedAction = $this->humanize($this->getAction());
        return $humanizedAction . ' ' . $this->getHumanizedArguments();
    }


    public function toString($maxLength)
    {
        $humanizedAction = $this->humanize($this->getAction());
        $maxLength = $maxLength - mb_strlen($humanizedAction, 'utf-8') - 1;
        return $humanizedAction . ' ' . $this->getHumanizedArguments($maxLength);
    }

    public function getHtml($highlightColor = '#732E81')
    {
        if (empty($this->arguments)) {
            return sprintf('%s %s', ucfirst($this->prefix), $this->humanize($this->getAction()));
        }

        return sprintf('%s %s <span style="color: %s">%s</span>', ucfirst($this->prefix), htmlspecialchars($this->humanize($this->getAction()), ENT_QUOTES | ENT_SUBSTITUTE), $highlightColor, htmlspecialchars($this->getHumanizedArguments(0), ENT_QUOTES | ENT_SUBSTITUTE));
    }

    public function getHumanizedActionWithoutArguments()
    {
        return $this->humanize($this->getAction());
    }

    public function getHumanizedArguments($maxLength = self::DEFAULT_MAX_LENGTH)
    {
        return $this->getArgumentsAsString($maxLength);
    }

    protected function clean($text)
    {
        return str_replace('\/', '', $text);
    }

    protected function humanize($text)
    {
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        $text = preg_replace('~\bdont\b~', 'don\'t', $text);
        return mb_strtolower($text, 'UTF-8');
    }

    public function run(ModuleContainer $container = null)
    {
        $this->executed = true;
        if (!$container) {
            return null;
        }
        $activeModule = $container->moduleForAction($this->action);

        if (!is_callable([$activeModule, $this->action])) {
            throw new \RuntimeException("Action '{$this->action}' can't be called");
        }

        try {
            $res = call_user_func_array([$activeModule, $this->action], $this->arguments);
        } catch (\Exception $e) {
            if ($this->isTry) {
                throw $e;
            }
            $this->failed = true;
            if ($this->getMetaStep()) {
                $this->getMetaStep()->setFailed(true);
            }
            throw $e;
        }

        return $res;
    }

    /**
     * If steps are combined into one method they can be reproduced as meta-step.
     * We are using stack trace to analyze if steps were called from test, if not - they were called from meta-step.
     *
     * @param $step
     * @param $stack
     */
    protected function addMetaStep($step, $stack)
    {
        if (($this->isTestFile($this->file)) || ($step['class'] == 'Codeception\Scenario')) {
            return;
        }

        $i = count($stack) - self::STACK_POSITION - 1;

        // get into test file and retrieve its actual call
        while (isset($stack[$i])) {
            $step = $stack[$i];
            $i--;
            if (!isset($step['file']) or !isset($step['function']) or !isset($step['class'])) {
                continue;
            }

            if (!$this->isTestFile($step['file'])) {
                continue;
            }

            // in case arguments were passed by reference, copy args array to ensure dereference.  array_values() does not dereference values
            $this->metaStep = new Step\Meta($step['function'], array_map(function ($i) {
                return $i;
            }, array_values($step['args'])));
            $this->metaStep->setTraceInfo($step['file'], $step['line']);

            // pageobjects or other classes should not be included with "I"
            if (!in_array('Codeception\Actor', class_parents($step['class']))) {
                if (isset($step['object'])) {
                    $this->metaStep->setPrefix(get_class($step['object']) . ':');
                    return;
                }

                $this->metaStep->setPrefix($step['class'] . ':');
            }
            return;
        }
    }

    /**
     * @param MetaStep $metaStep
     */
    public function setMetaStep($metaStep)
    {
        $this->metaStep = $metaStep;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix . ' ';
    }
}
