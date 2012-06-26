@echo off
pushd .
cd %~dp0
cd "../Codeception/Codeception"
set BIN_TARGET=%CD%\codecept
popd
php "%BIN_TARGET%" %*
