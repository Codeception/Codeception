<?php
namespace Codeception\Module;

use Codeception\Module;

/**
 * Special module for using asserts in your tests.
 *
 * Class Asserts
 * @package Codeception\Module
 */
class Asserts extends Module {

    use \Codeception\Util\Shared\Asserts {
        assertEquals as public seeEquals;
        assertNotEquals as public dontSeeEquals;
        assertGreaterThen as public seeGreaterThen;
        assertGreaterThenOrEqual as public seeGreaterThenOrEqual;
        assertContains as public seeContains;
        assertNotContains as public dontSeeContains;
        assertEmpty as public seeEmpty;
        assertNotEmpty as public dontSeeEmpty;
        assertNull as public seeNull;
        assertNotNull as public dontSeeNull;
        assertTrue as public seeTrue;
        assertFalse as public seeFalse;
        fail as public;
    }
} 