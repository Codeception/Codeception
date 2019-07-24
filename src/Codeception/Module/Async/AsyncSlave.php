<?php

namespace Codeception\Module\Async;

use function call_user_func_array;

class AsyncSlave
{
    /**
     * @var AsyncSlave
     */
    private static $singleton;

    /**
     * @var string
     */
    private $inputFilename;

    /**
     * @var string
     */
    private $outputFilename;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var IPC
     */
    private $slaveController;

    /**
     * @param string $inputFilename
     * @param string $outputFilename
     * @param string $class
     * @param string $method
     */
    public function __construct($inputFilename, $outputFilename, $class, $method)
    {
        self::$singleton = $this;
        $this->inputFilename = $inputFilename;
        $this->outputFilename = $outputFilename;
        $this->class = $class;
        $this->method = $method;
        $this->slaveController = new IPC($inputFilename, $outputFilename);
    }

    public function run($params)
    {
        call_user_func_array([$this->class, $this->method], $params);
    }

    public static function read()
    {
        return self::$singleton->slaveController->read();
    }

    public static function write($message)
    {
        self::$singleton->slaveController->write($message);
    }
}
