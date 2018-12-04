@echo off

if "%PHP_PEAR_PHP_BIN%" neq "" (
	set PHPBIN=%PHP_PEAR_PHP_BIN%
) else set PHPBIN=php

"%PHPBIN%" "codecept" %*
