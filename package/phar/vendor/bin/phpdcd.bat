@echo off
pushd .
cd %~dp0
cd "../EHER/PHPUnit/bin"
set BIN_TARGET=%CD%\phpdcd
popd
php "%BIN_TARGET%" %*
