<?php
require_once __DIR__.'/_data/MyOutputFormatter.php';
require_once __DIR__.'/_data/MyGroupHighlighter.php';

@unlink(\Codeception\Configuration::logDir().'order.txt');
$fh = fopen(\Codeception\Configuration::logDir().'order.txt', 'a');
fwrite($fh, 'B');
