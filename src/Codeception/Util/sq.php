<?php
use Codeception\Module\Sequence;

if (!function_exists('sq')) {
    function sq($id = null)
    {
        if ($id and isset(Sequence::$hash[$id])) {
            return Sequence::$hash[$id];
        }
        $prefix = str_replace('{id}', $id, Sequence::$prefix);
        $sequence = $prefix . uniqid($id);
        if ($id) {
            Sequence::$hash[$id] = $sequence;
        }
        return $sequence;
    }
}

if (!function_exists('sqs')) {
    function sqs($id = null)
    {
        if ($id and isset(Sequence::$suiteHash[$id])) {
            return Sequence::$suiteHash[$id];
        }
        $prefix = str_replace('{id}', $id, Sequence::$prefix);
        $sequence = $prefix . uniqid($id);
        if ($id) {
            Sequence::$suiteHash[$id] = $sequence;
        }
        return $sequence;
    }
}
