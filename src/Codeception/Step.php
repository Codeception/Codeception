<?php
namespace Codeception;

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

    public function __construct($action, array $arguments)
    {
        $this->action    = $action;
        $this->arguments = $arguments;
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
        if (is_callable($argument, true)) {
            return 'lambda function';  
        } 

        if (!is_object($argument)) {
            return $argument;
        }

        if (method_exists($argument, '__toString')) {
            return $argument->__toString();    
        } 

        return (isset($argument->__mocked)) ? $this->formatClassName($argument->__mocked) : $this->formatClassName(get_class($argument));
    }

    protected function formatClassName($classname)
    {
        return trim($classname, "\\");
    }

    public function __toString()
    {
        return "I " . $this->getHumanizedAction();
    }

    public function getHumanizedAction()
    {
        return $this->humanize($this->getAction()) . ' ' . $this->getHumanizedArguments();
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

    public function run()
    {
        $this->executed = true;
        $activeModule   = \Codeception\SuiteManager::$modules[\Codeception\SuiteManager::$actions[$this->action]];

        if (!is_callable(array($activeModule, $this->action))) {
            throw new \RuntimeException("Action can't be called");  
        } 
        
        return call_user_func_array(array($activeModule, $this->action), $this->arguments);
    }
}
