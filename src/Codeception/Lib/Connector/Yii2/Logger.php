<?php
namespace Codeception\Lib\Connector\Yii2;

use Codeception\Util\Debug;

class Logger extends \yii\log\Logger
{
    public function init()
    {
        // overridden to prevent register_shutdown_function
    }

    public function log($message, $level, $category = 'application')
    {
        if (!in_array($level, [
            \yii\log\Logger::LEVEL_INFO,
            \yii\log\Logger::LEVEL_WARNING,
            \yii\log\Logger::LEVEL_ERROR,
        ])) {
            return;
        }
        if (strpos($category, 'yii\db\Command')===0) {
            return; // don't log queries
        }

        // https://github.com/Codeception/Codeception/issues/3696
        if ($message instanceof \yii\base\Exception) {
            $message = $message->__toString();
        }

        Debug::debug("[$category] " .  \yii\helpers\VarDumper::export($message));
    }
}
