@echo off
pushd .
cd %~dp0
cd "../phpunit/phpunit/composer/bin"
set BIN_TARGET=%CD%\phpunit
popd
php "%BIN_TARGET%" %*
