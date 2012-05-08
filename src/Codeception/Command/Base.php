<?php
namespace Codeception\Command;

use \Symfony\Component\Yaml\Yaml;

class Base extends \Symfony\Component\Console\Command\Command
{
    protected function initialize($input, $output)
    {
//        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
//            exec('chcp 65001');
//        }
    }
}
