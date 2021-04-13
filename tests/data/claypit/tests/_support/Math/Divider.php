<?php

namespace Math;

class Divider
{
    public function perfom($a, $b)
    {
        return $b !== 0 ? $a / $b : 0;
    }
}
