<?php

declare(strict_types=1);

use Codeception\Config\GlobalConfig;
use Codeception\Config\SuiteConfig;

return GlobalConfig::create()
    ->namespace('PhpConfig')
    ->supportNamespace('Support')
    ->paths(
        tests: 'tests',
        output: 'tests/_output',
        data: 'tests/_data',
        support: 'tests/_support',
    )
    ->settings(colors: false, lint: false)
    ->suite('Sample', SuiteConfig::create()
        ->actor('SampleTester')
        ->module('Asserts')
        ->merge(['inline_marker' => 'INLINE_WINS']));
