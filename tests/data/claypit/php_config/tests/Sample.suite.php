<?php

declare(strict_types=1);

use Codeception\Config\SuiteConfig;

return SuiteConfig::create()
    ->actor('SampleTester')
    ->module('Asserts');
