<?php
use \Codeception\Module\Sequence;

function sq($id = null)
{
    if ($id and isset(Sequence::$hash[$id])) return Sequence::$hash[$id];
    $sequence = '_'.uniqid();
    if ($id) Sequence::$hash[$id] = $sequence;
    return $sequence;
}