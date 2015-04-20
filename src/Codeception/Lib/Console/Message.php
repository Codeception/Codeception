<?php

namespace Codeception\Lib\Console;

use Symfony\Component\Console\Output\OutputInterface;

class Message
{
    protected $output;
    protected $message;

    public function __construct($message, Output $output = null)
    {
        $this->message = $message;
        $this->output  = $output;
    }

    public function with($param)
    {
        $args          = array_merge(array($this->message), func_get_args());
        $this->message = call_user_func_array('sprintf', $args);

        return $this;
    }

    public function style($name)
    {
        $this->message = sprintf('<%s>%s</%s>', $name, $this->message, $name);

        return $this;
    }

    public function width($length, $char = ' ')
    {
        $message_length = strlen(strip_tags($this->message));
        if ($message_length < $length) {
            $this->message .= str_repeat($char, $length - $message_length);
        }
        return $this;
    }

    public function write($verbose = OutputInterface::VERBOSITY_NORMAL)
    {
        if ($verbose > $this->output->getVerbosity()) {
            return;
        }
        $this->output->write($this->message);
    }

    public function writeln($verbose = OutputInterface::VERBOSITY_NORMAL)
    {
        if ($verbose > $this->output->getVerbosity()) {
            return;
        }
        $this->output->writeln($this->message);
    }

    public function prepend($string)
    {
        if ($string instanceof Message) {
            $string = $string->getMessage();
        }
        $this->message = $string . $this->message;
        return $this;
    }

    public function append($string)
    {
        if ($string instanceof Message) {
            $string = $string->getMessage();
        }
        $this->message .= $string;

        return $this;
    }

    public function apply($func)
    {
        $this->message = call_user_func($func, $this->message);
        return $this;
    }

    public function center($char)
    {
        $this->message = $char . $this->message . $char;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function block($style)
    {
        $this->message = $this->output->formatHelper->formatBlock($this->message, $style, true);

        return $this;
    }

    public function getLength()
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($this->message);
        }
        return strlen($this->message);
    }

    public function widthWithTerminalCorrection($width, $char = ' ')
    {
        $cols = 0;
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $cols = intval(`command -v tput >> /dev/null && tput cols`);
        }
        if ($cols > 0) {
            $const = ($char == ' ') ? 6 : 1;
            $width = ($cols <= $width) ? $cols - $const : $width;
            $width = ($width < $const) ? $const : $width;
        }
        return $this->width($width, $char);
    }

    public function __toString()
    {
        return $this->message;
    }
}
