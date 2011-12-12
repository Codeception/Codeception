<?php
namespace Codeception;
 
class Output {

	protected $silent = false;
    protected $colors = true;

	function __construct($silent = false, $colors = true) {
	    $this->silent = $silent;
	    $this->colors = $colors;
	}

	public function put($message) {
		if ($this->silent) return;
        $message = $this->colors ? $this->colorize($message) : $this->naturalize($message);
		$message = $this->clean($message);
		$this->write($message);


	}

	private function write($text)
	{
		if ($this->silent) return;
        ob_get_flush();
        print $text;
        ob_start();
	}

    protected function naturalize($message)
    {
		$message = str_replace(array('[[',']]','(%','%)','((','))','(!','!)'), array('','','','','','','',''), $message);
		return $message;
    }

	protected function colorize($message) {
		// magent colors
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

	public function writeln($message) {
		$this->put("\n# $message");

	}

	public function debug($message) {
        if ($this->silent) return;
		if (is_array($message)) $message = implode("\n=> ", $message);
        $this->colors ? $this->write("\033[36m\n=> ".$message."\033[0m") : $this->write("=> ".$message) ;
	}


}
