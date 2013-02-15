<?php
namespace Codeception;

abstract class Step
{
    const ACTION    = 'Action';
    const COMMENT   = 'Comment';
    const ASSERTION = 'Assertion';

    /**
     * @var    string
     */
    protected $action;

    /**
     * @var    array
     */
    protected $arguments;

    public $executed = false;

    public function __construct($action, array $arguments)
    {
        $this->action = $action;
        $this->arguments = $arguments;
    }

    /**
     * Returns this step's action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Returns this step's arguments.
     *
     * @param  boolean $asString
     * @return array|string
     */
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
                // if (settype($argument, 'string') === false) throw new \InvalidArgumentException('Argument can\'t be converted to string or serialized');
            }
            return stripcslashes(trim(json_encode($arguments, JSON_UNESCAPED_UNICODE),'[]'));
        }
    }

    protected function formatClassName($classname)
    {
        return trim($classname,"\\");
    }

    abstract public function getName();

    public function __toString()
    {
        return "I " . $this->getHumanizedAction();
    }

    public function getHumanizedAction()
    {
        return $this->humanize($this->getAction()). ' ' . $this->getHumanizedArguments();
    }

    public function getHtmlAction() {
        $args = $this->getHumanizedArguments();
        $args = preg_replace('~\$(.*?)\s~','$<span style="color: #3C3C89; font-weight: bold;">$1</span>', $args);
        return $this->humanize($this->getAction()). ' <span style="color: #732E81;">'.$args.'</span>';
    }

    public function getHumanizedActionWithoutArguments() {
        return $this->humanize($this->getAction());
    }
    
    public function getHumanizedArguments() {
        return $this->clean($this->getArguments(true));
    }

    protected function clean($text)
    {
        return str_replace('\/','',$text);
    }

    protected function humanize($text)
    {
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        $text = preg_replace('~\bdont\b~', 'don\'t', $text);
        return strtolower($text);
    }
    

}
