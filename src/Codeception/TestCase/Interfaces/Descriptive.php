<?php
namespace Codeception\TestCase\Interfaces;

interface Descriptive
{
    public function getFileName();
    public function getSignature();
    public function getName($withDataSet = TRUE);
    public function toString();
}