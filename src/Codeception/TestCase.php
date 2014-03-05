<?php

namespace Codeception;

use Codeception\Event\StepEvent;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\TestCase\Shared\Dependencies;
use Symfony\Component\EventDispatcher\Event;

abstract class TestCase extends \PHPUnit_Framework_TestCase implements \PHPUnit_Framework_SelfDescribing
{
    protected $backupGlobalsBlacklist = array('app');
}
