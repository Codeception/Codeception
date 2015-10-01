<?php
namespace Codeception\Test\Loader;

interface LoaderInterface
{
    public function loadTests($filename);

    public function getTests();

    public function getPattern();
}