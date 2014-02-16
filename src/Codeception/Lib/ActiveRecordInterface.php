<?php
namespace Codeception\Lib;

interface ActiveRecordInterface {
    public function haveRecord($model, $attributes = array());
    public function seeRecord($model, $attributes = array());
    public function dontSeeRecord($model, $attributes = array());
    public function grabRecord($model, $attributes = array());

} 