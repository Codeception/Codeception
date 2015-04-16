<?php
namespace Codeception;

use Codeception\Lib\ModuleContainer;

abstract class Step
{
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

    protected $failed = false;

    public function __construct($action, array $arguments)
    {
        $this->action = $action;
        $this->arguments = $arguments;
        $this->saveLineNumber();
    }

    protected function saveLineNumber()
    {
        if (!function_exists('xdebug_get_function_stack')) {
            return;
        }

        $stack = xdebug_get_function_stack();
        if (count($stack) < 4) {
            return;
        }
        $this->line = $stack[count($stack)-3]['line'];
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

    public function getLineNumber()
    {
        return $this->line;

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
            $arguments[$key] = (is_string($argument)) ? $argument : $this->parseArgumentAsString($argument);
        }

        if (defined('JSON_UNESCAPED_UNICODE')) {
            return stripcslashes(trim(json_encode($arguments, JSON_UNESCAPED_UNICODE), '[]'));
        }

        return stripcslashes(trim(json_encode($arguments), '[]'));
    }

    protected function parseArgumentAsString($argument)
    {
        if (is_object($argument) && method_exists($argument, '__toString')) {
            return (string)$argument;
        } elseif (is_callable($argument, true)) {
            return 'lambda function';
        } elseif (!is_object($argument)) {
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
        return "\$I->" . $this->getAction() . '(' . $this->getHumanizedArguments() .')';
    }

    public function __toString()
    {
        return 'I ' . $this->humanize($this->getAction()) . ' ' . $this->getHumanizedArguments();
    }

    public function getHtmlAction()
    {
        $args = preg_replace('~\$(.*?)\s~', '$<span style="color: #3C3C89; font-weight: bold;">$1</span>', $this->getHumanizedArguments());
        return $this->humanize($this->getAction()) . ' <span style="color: #732E81;">' . $args . '</span>';
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
}
