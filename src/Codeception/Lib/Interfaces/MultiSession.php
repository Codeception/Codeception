<?php
namespace Codeception\Lib\Interfaces;

interface MultiSession
{
    public function _initializeSession();

    public function _loadSession($session);

    public function _backupSession();

    public function _closeSession($session = null);

    public function _getName();
}
