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
        $this->output = $output;
    }

    public function with($param)
    {
        $args = array_merge([$this->message], func_get_args());
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
        $message_length = $this->getLength();

        if ($message_length < $length) {
            $this->message .= str_repeat($char, $length - $message_length);
        }
        return $this;
    }

    public function cut($length)
    {
        $this->message = mb_substr($this->message, 0, $length, 'utf-8');
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

    public function getLength($includeTags = false)
    {
        return mb_strwidth($includeTags ? $this->message : strip_tags($this->message), 'utf-8');
    }

    public static function ucfirst($text)
    {
        return mb_strtoupper(mb_substr($text, 0, 1, 'utf-8'), 'utf-8') . mb_substr($text, 1, null, 'utf-8');
    }

    public function __toString()
    {
        return $this->message;
    }
}
