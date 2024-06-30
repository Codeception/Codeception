<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

use Symfony\Component\Console\Formatter\OutputFormatter;

class Colorizer
{
    public function colorize(string $string = ''): string
    {
        $lines = explode("\n", $string);
        $colorizedMessage = '';

        foreach ($lines as $line) {
            $char = $line[0] ?? '';
            $line = OutputFormatter::escape(trim($line));

            switch ($char) {
                case '+':
                    $line = "<info>{$line}</info>";
                    break;
                case '-':
                    $line = "<comment>{$line}</comment>";
                    break;
            }

            $colorizedMessage .= $line . "\n";
        }

        return trim($colorizedMessage);
    }
}
