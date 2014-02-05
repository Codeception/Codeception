<?php

namespace Codeception\Util;

interface MultiSessionInterface
{
    public function _initializeSession();

    public function _loadSessionData($data);

    public function _backupSessionData();

    public function _closeSession($data);

    public function getName();
}
