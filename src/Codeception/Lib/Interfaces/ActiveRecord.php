<?php
namespace Codeception\Lib\Interfaces;

interface ActiveRecord extends ORM
{
    public function haveRecord($model, $attributes = []);

    public function seeRecord($model, $attributes = []);

    public function dontSeeRecord($model, $attributes = []);

    public function grabRecord($model, $attributes = []);
}
