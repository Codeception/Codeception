<?php
require_once __DIR__.'/_data/MyGroupHighlighter.php';
require_once __DIR__.'/_data/VerbosityLevelOutput.php';

@unlink(\Codeception\Configuration::outputDir().'order.txt');
$fh = fopen(\Codeception\Configuration::outputDir().'order.txt', 'a');
fwrite($fh, 'B');
