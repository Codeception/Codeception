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
        $this->action = $action;
        $this->arguments = $arguments;
    }

    public function getName()
    {
        $class = explode('\\', __CLASS__);
        return end($class);
    }

    public function pullDebugOutput()
    {
        $output = $this->debugOutput;
        $this->debugOutput = null;
        return $output;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getArguments($asString = FALSE)
    {
        if (!$asString) {
            return $this->arguments;
        } else {
            $arguments = $this->arguments;

            foreach ($arguments as $k => $argument) {
                if (!is_string($argument) and is_callable($argument, true)) {
                    $arguments[$k] = 'lambda function';
                    continue;
                }
                if (is_object($argument)) {
                    if (method_exists($argument, '__toString')) {
                        $arguments[$k] = $argument->__toString();
                    } elseif (isset($argument->__mocked)) {
                        $arguments[$k] = $this->formatClassName($argument->__mocked);
                    } else {
                        $arguments[$k] = $this->formatClassName(get_class($argument));
                    }
                    continue;
                }
            }
            if (defined('JSON_UNESCAPED_UNICODE')) {
                return stripcslashes(trim(json_encode($arguments, JSON_UNESCAPED_UNICODE), '[]'));
            }
            return stripcslashes(trim(json_encode($arguments), '[]'));
        }
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
        $args = $this->getHumanizedArguments();
        $args = preg_replace('~\$(.*?)\s~', '$<span style="color: #3C3C89; font-weight: bold;">$1</span>', $args);
        return $this->humanize($this->getAction()) . ' <span style="color: #732E81;">' . $args . '</span>';
    }

    public function getHumanizedActionWithoutArguments()
    {
        return $this->humanize($this->getAction());
    }

    public function getHumanizedArguments()
    {
        $args = array_map(function ($a) { return '"'.$a.'"'; }, $this->getArguments());
        $args = implode(' within ', $args);
        return $this->clean($args);
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
        $activeModule = \Codeception\SuiteManager::$modules[\Codeception\SuiteManager::$actions[$this->action]];

        if (is_callable(array($activeModule, $this->action))) {
            $result = call_user_func_array(array($activeModule, $this->action), $this->arguments);
        } else {
            throw new \RuntimeException("Action can't be called");
        }
        $this->debugOutput = $activeModule->_getDebugOutput();
        return $result;
    }

}
