<?php

namespace Codeception\Lib\Interfaces;

interface MultiSession
{
    public function _initializeSession(): void;

    public function _loadSession(string $session): void;

    public function _backupSession(): void;

    public function _closeSession(string $session = null): void;

    public function _getName(): void;
}
