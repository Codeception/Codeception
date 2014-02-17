<?php
use \Codeception\Module\Sequence;

if (!function_exists('sq')) {
function sq($id = null)
{
    if ($id and isset(Sequence::$hash[$id])) {
        return Sequence::$hash[$id];
    }
    $sequence = '_' . uniqid();
    if ($id) {
        Sequence::$hash[$id] = $sequence;
    }
    return $sequence;
}

}