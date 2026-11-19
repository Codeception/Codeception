<?php

declare(strict_types=1);

use Codeception\Config\GlobalConfig;

return GlobalConfig::create()
    ->namespace('PhpConfig')
    ->supportNamespace('Support')
    ->paths(
        tests: 'tests',
        output: 'tests/_output',
        data: 'tests/_data',
        support: 'tests/_support',
        envs: 'tests/_envs',
    )
    ->settings(colors: false, lint: false)
    ->merge(['marker' => 'FROM_PHP']);
