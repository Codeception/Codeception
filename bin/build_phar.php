#!/usr/bin/env php
<?php

if (file_exists('../package/codecept.phar')) unlink('../package/codecept.phar');

$p = new Phar('../package/codecept.phar');
$p->startBuffering();
$p->buildFromDirectory('..','~\.php$~');
$p->setStub(file_get_contents('../package/stub.php'));
$p->stopBuffering();
$p->compressFiles(Phar::GZ);