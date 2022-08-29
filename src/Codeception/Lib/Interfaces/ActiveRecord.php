<?php

namespace Codeception\Lib\Interfaces;

interface ActiveRecord extends ORM
{
    public function haveRecord(string $model, array $attributes = []);

    public function seeRecord(string $model, array $attributes = []): void;

    public function dontSeeRecord(string $model, array $attributes = []): void;

    public function grabRecord(string $model, array $attributes = []);
}
