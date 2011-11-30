<?php
namespace Codeception\Util\Stub;

use Symfony\Component\Finder\Finder;

class Builder
{

    public static function loadClasses($provider = 'phpunit')
    {
        require_once __DIR__.'/builders/'.$provider.'/Stub.php';
    }
}
