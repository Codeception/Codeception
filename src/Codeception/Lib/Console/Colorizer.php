<?php
namespace Codeception\Lib\Console;

/**
 * Colorizer
 **/
class Colorizer
{
    /**
     * @param string $string
     * @return string
     */
    public function colorize($string = '')
    {
        $fp = fopen('php://memory', 'r+');
        fwrite($fp, $string);
        rewind($fp);

        $colorizedMessage = '';
        while ($line = fgets($fp)) {
            $char = $line[0];
            $line = trim($line);

            switch ($char) {
                case '+':
                    $line = "<info>$line</info>";
                    break;
                case '-':
                    $line = "<comment>$line</comment>";
                    break;
            }

            $colorizedMessage .= $line . "\n";
        }

        return trim($colorizedMessage);
    }
}
