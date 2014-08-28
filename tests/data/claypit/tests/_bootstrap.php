<?php
include_once __DIR__ . '/printers/CustomPrinter.php';
require_once __DIR__.'/_data/MyGroupHighlighter.php';

@unlink(\Codeception\Configuration::outputDir().'order.txt');
$fh = fopen(\Codeception\Configuration::outputDir().'order.txt', 'a');
fwrite($fh, 'B');
