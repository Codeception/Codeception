<?php
namespace Codeception\Module;

use Codeception\Module;

/**
 * Special module for using asserts in your tests.
 *
 */
class Asserts extends Module
{
    use \Codeception\Util\Shared\Asserts {
        assertEquals as public;
        assertNotEquals as public;
        assertGreaterThen as public;
        assertGreaterThenOrEqual as public;
        assertContains as public;
        assertNotContains as public;
        assertEmpty as public;
        assertNotEmpty as public;
        assertNull as public;
        assertNotNull as public;
        assertTrue as public;
        assertFalse as public;
        fail as public;
    }
} 