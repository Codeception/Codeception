<?php

namespace Codeception\Test\Interfaces;

interface StrictCoverage
{
    public function getLinesToBeCovered(): array;

    public function getLinesToBeUsed(): array;
}
