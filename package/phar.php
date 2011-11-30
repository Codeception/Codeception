#!/usr/bin/env php
<?php

if (file_exists('codecept.phar')) unlink('codecept.phar');

$p = new Phar('codecept.phar');
$p->startBuffering();
$p->buildFromDirectory('..','~\.php$~');
$p->setStub(file_get_contents('stub.php'));
$p->stopBuffering();
$p->compressFiles(Phar::GZ);