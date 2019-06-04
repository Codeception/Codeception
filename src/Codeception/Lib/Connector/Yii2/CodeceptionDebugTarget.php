<?php


namespace Codeception\Lib\Connector\Yii2;


use yii\log\Target;

/**
 * Class CodeceptionDebugTarget
 * This class allows printing Yii log messages to Codeceptions' debug output.
 * @package Codeception\Lib\Connector\Yii2
 */
class CodeceptionDebugTarget extends Target
{
    public $exportInterval = 1;

    public $levels = [
        \yii\log\Logger::LEVEL_INFO,
        \yii\log\Logger::LEVEL_WARNING,
        \yii\log\Logger::LEVEL_ERROR
    ];

    public $except = [
        'yii\db\Command*'
    ];

    /**
     * Exports log [[messages]] to a specific destination.
     * Child classes must implement this method.
     */
    public function export()
    {
        foreach($this->messages as $message) {
            codecept_debug($this->formatMessage($message));
        }
    }
}