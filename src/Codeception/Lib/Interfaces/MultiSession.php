<?php

namespace Codeception\Lib\Interfaces;

interface MultiSession
{
    public function _initializeSession();

    public function _loadSessionData($data);

    public function _backupSessionData();

    public function _closeSession($data);

    public function _getName();
}
