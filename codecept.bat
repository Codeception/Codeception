@echo off
if "%PHPBIN%" == "" set PHPBIN=@php_bin@
if exist "codecept" goto INTERNAL
if not exist "%PHPBIN%" if "%PHP_PEAR_PHP_BIN%" neq "" goto USE_PEAR_PATH
GOTO RUN
:USE_PEAR_PATH
set PHPBIN=%PHP_PEAR_PHP_BIN%
:RUN
"%PHPBIN%" "@bin_dir@\codecept" %*
:INTERNAL
"%PHPBIN%" "codecept" %*