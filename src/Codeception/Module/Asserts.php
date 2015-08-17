<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use \Codeception\Util\Shared\Asserts as SharedAsserts;

/**
 * Special module for using asserts in your tests.
 *
 */
class Asserts extends CodeceptionModule
{
    use SharedAsserts {
        assertEquals as public;
        assertNotEquals as public;
        assertSame as public;
        assertNotSame as public;
        assertGreaterThan as public;
        assertGreaterThen as public;
        assertGreaterThanOrEqual as public;
        assertGreaterThenOrEqual as public;
        assertLessThan as public;
        assertLessThanOrEqual as public;
        assertContains as public;
        assertNotContains as public;
        assertRegExp as public;
        assertNotRegExp as public;
        assertEmpty as public;
        assertNotEmpty as public;
        assertNull as public;
        assertNotNull as public;
        assertTrue as public;
        assertFalse as public;
        assertFileExists as public;
        assertFileNotExists as public;
        fail as public;
    }
}
