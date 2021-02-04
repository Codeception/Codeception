<?php

namespace Codeception\Test\Loader;

interface LoaderInterface
{
    public function loadTests(string $filename);

    public function getTests();

    public function getPattern();
}
