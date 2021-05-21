<?php

namespace Codeception\Test\Interfaces;

interface Dependent
{
    public function fetchDependencies(): array;
}
