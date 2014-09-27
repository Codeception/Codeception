<?php
namespace Codeception\PHPUnit\ResultPrinter;

use Codeception\Configuration;

class CustomPrinter extends \Codeception\PHPUnit\ResultPrinter\HTML
{
    /**
     * Constructor.
     *
     * @param  mixed $out
     * @throws \InvalidArgumentException
     */
    public function __construct($out = null)
    {
        if ($out !== null && $out !== true) {
            if (is_string($out)) {
                if (strpos($out, 'socket://') === 0) {
                    $out = explode(':', str_replace('socket://', '', $out));

                    if (sizeof($out) != 2) {
                        throw new \PHPUnit_Framework_Exception;
                    }

                    $this->out = fsockopen($out[0], $out[1]);
                } else {
                    if (strpos($out, 'php://') === false &&
                        !is_dir(dirname($out))
                    ) {
                        mkdir(dirname($out), 0777, true);
                    }

                    $this->out = fopen($out, 'wt');
                }

                $this->outTarget = $out;
            } else {
                $this->out = $out;
            }
        } else {
            $out = Configuration::logDir()  . date(
                    'Y-m-d',
                    time()
                ) . DIRECTORY_SEPARATOR . 'report.html';
            mkdir(dirname($out), 0777, true);
            $this->out = fopen($out, 'wt');
            $this->outTarget = $out;
        }

        $this->prettifier = new \PHPUnit_Util_TestDox_NamePrettifier;
        $this->startRun();

        $this->templatePath = sprintf(
            '%s%stemplate%s',
            dirname(__FILE__),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );
    }

}
