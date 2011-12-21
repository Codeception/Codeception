#!/usr/bin/env php
<?php

if (file_exists(__DIR__.'/../package/codecept.phar')) unlink(__DIR__.'/../package/codecept.phar');

$p = new Phar(__DIR__.'/../package/codecept.phar');
$p->startBuffering();
$p->buildFromDirectory(__DIR__.'/..','~\.php$~');
$p->addFile(__DIR__.'/../codecept','codecept');
$p->setStub(file_get_contents(__DIR__.'/../package/stub.php'));
$p->stopBuffering();
$p->compressFiles(Phar::GZ);