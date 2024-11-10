<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

use Exception;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper as SymfonyFormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class Output extends ConsoleOutput
{
    /**
     * @var array<string, int|bool>
     */
    protected array $config = [
        'colors'      => true,
        'verbosity'   => self::VERBOSITY_NORMAL,
        'interactive' => true
    ];

    public SymfonyFormatterHelper $formatHelper;

    public bool $waitForDebugOutput = true;

    protected bool $isInteractive = false;

    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);

        $this->isInteractive = $this->config['interactive']
            && isset($_SERVER['TERM'])
            && PHP_SAPI === 'cli'
            && $_SERVER['TERM'] != 'linux';

        $formatter = new OutputFormatter($this->config['colors']);
        $this->configureStyles($formatter);

        $this->formatHelper = new SymfonyFormatterHelper();
        parent::__construct($this->config['verbosity'], $this->config['colors'], $formatter);
    }

    protected function configureStyles(OutputFormatter $formatter): void
    {
        $formatter->setStyle('default', new OutputFormatterStyle());
        $formatter->setStyle('bold', new OutputFormatterStyle(null, null, ['bold']));
        $formatter->setStyle('focus', new OutputFormatterStyle('magenta', null, ['bold']));
        $formatter->setStyle('ok', new OutputFormatterStyle('green', null, ['bold']));
        $formatter->setStyle('error', new OutputFormatterStyle('white', 'red', ['bold']));
        $formatter->setStyle('fail', new OutputFormatterStyle('red', null, ['bold']));
        $formatter->setStyle('pending', new OutputFormatterStyle('yellow', null, ['bold']));
        $formatter->setStyle('debug', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('comment', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('info', new OutputFormatterStyle('green'));
    }

    protected function clean(string $message): string
    {
        return str_replace('\/', '/', $message);
    }

    public function isInteractive(): bool
    {
        return $this->isInteractive;
    }

    public function debug(mixed $message): void
    {
        if ($this->waitForDebugOutput) {
            $this->writeln('');
            $this->waitForDebugOutput = false;
        }

        if (!is_string($message)) {
            dump($message);
            return;
        }

        $message = $this->clean($message);
        $message = OutputFormatter::escape($message);
        $this->writeln("<debug>  {$message}</debug>");
    }

    public function message($message): Message
    {
        $message = sprintf(...func_get_args());
        return new Message($message, $this);
    }

    public function exception(Exception $exception): void
    {
        $class = $exception::class;
        $this->writeln("");
        $this->writeln(sprintf('(![ %s ]!)', $class));
        $this->writeln($exception->getMessage());
        $this->writeln("");
    }

    public function notification(string $message): void
    {
        $this->writeln("<comment>{$message}</comment>");
    }
}
