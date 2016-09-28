<?php

namespace Codeception\Test\Interfaces;

interface StrictCoverage
{
    public function getLinesToBeCovered();

    public function getLinesToBeUsed();
}
