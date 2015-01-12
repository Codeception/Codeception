<?php

namespace Codeception\Util\SQL;

/**
 * class AsIs
 *
 * @author    Dino Korah <dino.korah@redmatter.com>
 * @copyright 2009-2014 Red Matter Ltd (UK)
 */
class AsIs
{
    protected $sqlFragment;

    public function __construct($sqlFragment)
    {
        $this->sqlFragment = $sqlFragment;
    }

    public function __toString()
    {
        return $this->sqlFragment;
    }
}
