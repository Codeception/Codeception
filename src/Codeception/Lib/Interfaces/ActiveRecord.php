<?php
namespace Codeception\Lib\Interfaces;

interface ActiveRecord
{
    public function haveRecord($model, $attributes = []);

    public function seeRecord($model, $attributes = []);

    public function dontSeeRecord($model, $attributes = []);

    public function grabRecord($model, $attributes = []);

} 