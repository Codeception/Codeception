<?php
namespace Codeception;
 
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Output implements OutputInterface {

    protected $colors = true;
	protected $defer_flush = false;

	function __construct($colors = true, $defer_flush = false) {
	    $this->colors = $colors;
	    $this->defer_flush = $defer_flush;
        ob_start();
	}

	public function put($message) {
        $message = $this->colors ? $this->colorize($message) : $this->naturalize($message);
		$message = $this->clean($message);
		$this->write($message);
	}

    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
	{
        if (!$this->defer_flush) {
            while (ob_get_level()) ob_end_flush();
        }
        print $messages;
        ob_start();
	}

    protected function naturalize($message)
    {
		$message = str_replace(array('[[',']]','(%','%)','((','))','(!','!)'), array('','','','','','','',''), $message);
		return $message;
    }

	protected function colorize($message) {
		// magenta colors
		$message = str_replace(array('[[',']]'), array("\033[35;1m","\033[0m"), $message);
		$message = str_replace(array('(%','%)'), array("\033[45;37m","\033[0m"), $message);
		// grey
		$message = str_replace(array('((','))'), array("\033[37;1m","\033[0m"), $message);
		$message = str_replace(array('(!','!)'), array("\033[41;37m","\033[0m"), $message);
		return $message;
	}

	protected function clean($message)
	{
		// clear json serialization
		$message = str_replace('\/','/', $message);
		return $message;
	}

    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
		$this->put("$messages\n");

	}

	public function debug($message) {
		if (is_array($message)) $message = implode("\n=> ", $message);
        $this->colors ? $this->writeln("\033[36m=> ".$message."\033[0m") : $this->writeln("=> ".$message) ;
	}

    public function isDecorated()
    {
        return false;
    }

    /**
     * Sets the verbosity of the output.
     *
     * @param integer $level The level of verbosity (one of the VERBOSITY constants)
     *
     * @api
     */
    public function setVerbosity($level)
    {
        // TODO: Implement setVerbosity() method.
    }

    /**
     * Gets the current verbosity of the output.
     *
     * @return integer The current level of verbosity (one of the VERBOSITY constants)
     *
     * @api
     */
    public function getVerbosity()
    {
        // TODO: Implement getVerbosity() method.
    }

    /**
     * Sets the decorated flag.
     *
     * @param Boolean $decorated Whether to decorate the messages
     *
     * @api
     */
    public function setDecorated($decorated)
    {
        // TODO: Implement setDecorated() method.
    }

    /**
     * Sets output formatter.
     *
     * @param OutputFormatterInterface $formatter
     *
     * @api
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        // TODO: Implement setFormatter() method.

    }

    /**
     * Returns current output formatter instance.
     *
     * @return  OutputFormatterInterface
     *
     * @api
     */
    public function getFormatter()
    {
        // TODO: Implement getFormatter() method.
    }
}
