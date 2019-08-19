<?php

@unlink(\Codeception\Configuration::outputDir().'order.txt');
$fh = fopen(\Codeception\Configuration::outputDir().'order.txt', 'a');
fwrite($fh, 'B');
