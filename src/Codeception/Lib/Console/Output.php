<?php
namespace Codeception\Lib\Console;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class Output extends ConsoleOutput
{
    protected $config = [
        'colors'      => true,
        'verbosity'   => self::VERBOSITY_NORMAL,
        'interactive' => true
    ];

    /**
     * @var \Symfony\Component\Console\Helper\FormatterHelper
     */
    public $formatHelper;

    public $waitForDebugOutput = true;

    protected $isInteractive = false;

    function __construct($config)
    {
        $this->config = array_merge($this->config, $config);

        // enable interactive output mode for CLI
        $this->isInteractive = $this->config['interactive'] && isset($_SERVER['TERM']) && php_sapi_name() == 'cli' && $_SERVER['TERM'] != 'linux';

        $formatter = new OutputFormatter($this->config['colors']);
        $formatter->setStyle('bold', new OutputFormatterStyle(null, null, ['bold']));
        $formatter->setStyle('focus', new OutputFormatterStyle('magenta', null, ['bold']));
        $formatter->setStyle('ok', new OutputFormatterStyle('white', 'magenta'));
        $formatter->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $formatter->setStyle('debug', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('comment', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('info', new OutputFormatterStyle('green'));

        $this->formatHelper = new FormatterHelper();


        parent::__construct($this->config['verbosity'], $this->config['colors'], $formatter);
    }

    public function isInteractive()
    {
        return $this->isInteractive;
    }

    protected function clean($message)
    {
        // clear json serialization
        $message = str_replace('\/', '/', $message);
        return $message;
    }

    public function debug($message)
    {
        $message = print_r($message, true);
        $message = str_replace("\n", "\n  ", $message);
        $message = $this->clean($message);
        if ($this->waitForDebugOutput) {
            $this->writeln('');
            $this->waitForDebugOutput = false;
        }
        $this->writeln("<debug>  $message</debug>");
    }

    function message($message)
    {
        $message = call_user_func_array('sprintf', func_get_args());
        return new Message($message, $this);
    }

    public function exception(\Exception $e)
    {
        $class = get_class($e);

        $this->writeln("");
        $this->writeln("(![ $class ]!)");
        $this->writeln($e->getMessage());
        $this->writeln("");
    }

    public function notification($message)
    {
        $this->writeln("<comment>$message</comment>");
    }
}
