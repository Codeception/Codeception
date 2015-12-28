<?php
namespace Codeception\Test\Loader;

interface Loader
{
    public function loadTests($filename);

    public function getTests();

    public function getPattern();
}