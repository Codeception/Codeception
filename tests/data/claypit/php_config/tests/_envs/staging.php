<?php

declare(strict_types=1);

use Codeception\Config\SuiteConfig;

return SuiteConfig::create()
    ->merge(['env_marker' => 'ENV_FROM_PHP']);
