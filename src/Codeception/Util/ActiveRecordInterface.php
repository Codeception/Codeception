<?php
namespace Codeception\Util;


interface ActiveRecordInterface {

    public function haveRecord($model, $attributes = array());
    public function seeRecord($model, $attributes = array());
    public function dontSeeRecord($model, $attributes = array());
    public function grabRecord($model, $attributes = array());

} 