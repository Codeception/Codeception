<?php

namespace Codeception\Module\Async;

use Codeception\Module\Async;
use Exception;
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

    /**
     * @param array $params
     * @throws Exception
     */
    public function run($params)
    {
        $result = call_user_func_array([$this->class, $this->method], $params);
        self::$singleton->slaveController->write(Async::RESULT_CHANNEL, $result);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function read()
    {
        return self::$singleton->slaveController->read(Async::MESSAGES_CHANNEL);
    }

    /**
     * @param mixed $message
     * @throws Exception
     */
    public static function write($message)
    {
        self::$singleton->slaveController->write(Async::MESSAGES_CHANNEL, $message);
    }
}
