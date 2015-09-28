<?php
namespace Codeception;

use Codeception\Lib\ModuleContainer;
use Codeception\Step\Meta;
use Codeception\Util\Locator;

abstract class Step
{
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
    protected $actor = 'I';

    /**
     * @var Meta
     */
    protected $metaStep = null;

    protected $failed = false;

    public function __construct($action, array $arguments)
    {
        $this->action = $action;
        $this->arguments = $arguments;
        $this->storeCallerInfo();
    }

    protected function storeCallerInfo()
    {
        if (!function_exists('xdebug_get_function_stack')) {
            return;
        }

        ini_set('xdebug.collect_params', '1');
        $stack = xdebug_get_function_stack();
        ini_set('xdebug.collect_params', 0);
        if (count($stack) <= self::STACK_POSITION) {
            return;
        }
        $traceLine = $stack[count($stack) - self::STACK_POSITION];

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

    public function getLine()
    {
        if ($this->line && $this->file) {
            return codecept_relative_path($this->file) . ':' . $this->line;
        }
    }

    public function hasFailed()
    {
        return $this->failed;
    }

    public function getArguments($asString = false)
    {
        return ($asString) ? $this->getArgumentsAsString($this->arguments) : $this->arguments;
    }

    protected function getArgumentsAsString(array $arguments)
    {
        foreach ($arguments as $key => $argument) {
            $arguments[$key] = (is_string($argument)) ? trim($argument,"''") : $this->parseArgumentAsString($argument);
        }

        return stripcslashes(trim(json_encode($arguments, JSON_UNESCAPED_UNICODE), '[]'));
    }

    protected function parseArgumentAsString($argument)
    {
        if (is_object($argument)) {
            if (method_exists($argument, '__toString')) {
                return (string)$argument;
            }
            if (get_class($argument) == 'Facebook\WebDriver\WebDriverBy') {
                return Locator::humanReadableString($argument);
            }
        }
        if (is_callable($argument, true)) {
            return 'lambda function';
        }
        if (!is_object($argument)) {
            return $argument;
        }

        return (isset($argument->__mocked)) ? $this->formatClassName($argument->__mocked) : $this->formatClassName(get_class($argument));
    }

    protected function formatClassName($classname)
    {
        return trim($classname, "\\");
    }

    public function getPhpCode()
    {
        return "\${$this->actor}->" . $this->getAction() . '(' . $this->getHumanizedArguments() .')';
    }

    /**
     * @return Meta
     */
    public function getMetaStep()
    {
        return $this->metaStep;

    }

    public function __toString()
    {
        return $this->actor . ' ' . $this->humanize($this->getAction()) . ' ' . $this->getHumanizedArguments();
    }

    public function getHtml($highlightColor = '#732E81')
    {
        return sprintf('%s %s <span style="color: %s">%s</span>', ucfirst($this->actor), $this->humanize($this->getAction()), $highlightColor, $this->getHumanizedArguments());
    }

    public function getHumanizedActionWithoutArguments()
    {
        return $this->humanize($this->getAction());
    }

    public function getHumanizedArguments()
    {
        return $this->clean($this->getArguments(true));
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
        return strtolower($text);
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
            $this->failed = true;
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
            if (!isset($step['file']) or !isset($step['function'])) {
                continue;
            }

            if (!$this->isTestFile($step['file'])) {
                continue;
            }

            $this->metaStep = new Meta($step['function'], array_values($step['params']));
            $this->metaStep->setTraceInfo($step['file'], $step['line']);

            // pageobjects or other classes should not be included with "I"
            if (!(new \ReflectionClass($step['class']))->isSubclassOf('Codeception\Actor')) {
                $this->metaStep->setActor($step['class'] . ':');
            }
            return;
        }
    }
}
