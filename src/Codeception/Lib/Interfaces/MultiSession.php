<?php

namespace Codeception\Lib\Interfaces;

interface MultiSession
{
    public function _initializeSession(): void;

    public function _loadSession($session): void;

    public function _backupSession();

    public function _closeSession($session = null): void;

    public function _getName(): string;
}
