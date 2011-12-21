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

    /**
     * Constructor.
     *
     * @param  array $arguments
     */
    public function __construct(array $arguments)
    {
        $this->action = array_shift($arguments);
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
                        $arguments[$k] = "(({$argument->__mocked}))";
                    } else {
                        $arguments[$k] = 'Instance of ' . get_class($argument);
                    }
                    continue;
                }
                // if (settype($argument, 'string') === false) throw new \InvalidArgumentException('Argument can\'t be converted to string or serialized');
            }

            switch (count($arguments)) {
                case 0:
                    return '';
                case 1:
                    return '"' . $arguments[0] . '"';
                default:

                    return json_encode($arguments);

            }
        }
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
    
    public function getHumanizedArguments() {
        return $this->clean($this->getArguments(true));
    }

    protected function clean($text)
    {
        return str_replace('\/', '/', $text);
    }

    protected function humanize($text)
    {
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        $text = preg_replace('~\bdont\b~', 'don\'t', $text);
        return strtolower($text);

    }

}
