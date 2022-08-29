<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

use Stringable;
use Symfony\Component\Console\Output\OutputInterface;

class Message implements Stringable
{
    public function __construct(protected string $message, protected ?Output $output = null)
    {
    }

    public function with($param): self
    {
        $args = array_merge([$this->message], func_get_args());
        $this->message = sprintf(...$args);
        return $this;
    }

    public function style(string $name): self
    {
        $this->message = sprintf('<%s>%s</%s>', $name, $this->message, $name);
        return $this;
    }

    public function width(int $length, string $char = ' '): self
    {
        $messageLength = $this->getLength();

        if ($messageLength < $length) {
            $this->message .= str_repeat($char, $length - $messageLength);
        }
        return $this;
    }

    public function cut(int $length): self
    {
        $this->message = mb_substr($this->message, 0, $length, 'utf-8');
        return $this;
    }

    public function write(int $verbose = OutputInterface::VERBOSITY_NORMAL): void
    {
        if ($verbose > $this->output->getVerbosity()) {
            return;
        }
        $this->output->write($this->message);
    }

    public function writeln(int $verbose = OutputInterface::VERBOSITY_NORMAL): void
    {
        if ($verbose > $this->output->getVerbosity()) {
            return;
        }
        $this->output->writeln($this->message);
    }

    public function prepend(Message|string $string): self
    {
        if ($string instanceof Message) {
            $string = $string->getMessage();
        }
        $this->message = $string . $this->message;
        return $this;
    }

    public function append(Message|string $string): self
    {
        if ($string instanceof Message) {
            $string = $string->getMessage();
        }
        $this->message .= $string;

        return $this;
    }

    public function apply(callable $func): self
    {
        $this->message = call_user_func($func, $this->message);
        return $this;
    }

    public function center(string $char): self
    {
        $this->message = $char . $this->message . $char;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function block(string $style): self
    {
        $this->message = $this->output->formatHelper->formatBlock($this->message, $style, true);

        return $this;
    }

    public function getLength(bool $includeTags = false): int
    {
        return mb_strwidth($includeTags ? $this->message : strip_tags($this->message), 'utf-8');
    }

    public static function ucfirst(string $text): string
    {
        return mb_strtoupper(mb_substr($text, 0, 1, 'utf-8'), 'utf-8') . mb_substr($text, 1, null, 'utf-8');
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
