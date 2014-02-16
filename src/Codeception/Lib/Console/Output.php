<?php
namespace Codeception\Lib\Console;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Output extends ConsoleOutput
{
    protected $config = array(
        'colors'    => true,
        'verbosity' => self::VERBOSITY_NORMAL
    );

    /**
     * @var \Symfony\Component\Console\Helper\FormatterHelper
     */
    public $formatHelper;

    function __construct($config)
    {
        $this->config = array_merge($this->config, $config);

        $formatter = new OutputFormatter($this->config['colors']);
        $formatter->setStyle('bold', new OutputFormatterStyle(null, null, array('bold')));
        $formatter->setStyle('focus', new OutputFormatterStyle('magenta', null, array('bold')));
        $formatter->setStyle('ok', new OutputFormatterStyle('white', 'magenta'));
        $formatter->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $formatter->setStyle('debug', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('info', new OutputFormatterStyle('yellow'));

        $this->formatHelper = new FormatterHelper();

        parent::__construct($this->config['verbosity'], $this->config['colors'], $formatter);
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
        $this->writeln("<debug>  $message</debug>");
    }

    function message($message)
    {
        $message = call_user_func_array('sprintf', func_get_args());
        return new Message($this, $message);
    }

    public function table(TableHelper $table)
    {
        $table->setLayout(TableHelper::LAYOUT_BORDERLESS);
        $table->setCellHeaderFormat('<info>%s</info>');
        $table->setCellRowFormat('%s');
        $table->render($this);
    }

    public function exception(\Exception $e)
    {
        $class = get_class($e);

        $this->writeln("");
        $this->writeln("(![ $class ]!)");
        $this->writeln($e->getMessage());
        $this->writeln("");
    }
}
