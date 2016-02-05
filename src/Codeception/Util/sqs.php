<?php
use Codeception\Module\Sequence;

if (!function_exists('sqs')) {
    function sqs($id = null)
    {
        if ($id and isset(Sequence::$suiteHash[$id])) {
            return Sequence::$suiteHash[$id];
        }
        $sequence = '_' . uniqid();
        if ($id) {
            Sequence::$suiteHash[$id] = $sequence;
        }
        return $sequence;
    }

}