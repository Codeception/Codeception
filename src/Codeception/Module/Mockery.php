<?php

namespace Codeception\Module;

use Codeception\Module;
use Codeception\TestCase;

/**
 * Integrates [Mockery](https://github.com/padraic/mockery) into Codeception tests.
 * 
 * Mockery is a simple yet flexible PHP mock object framework for use in unit testing.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **stable**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Example (`unit.suite.yml`)
 * 
 *     modules:
 *        enabled: [Mockery]
 * 
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Mockery extends Module
{
    public function _after(TestCase $test)
    {
        \Mockery::close();
    }
}
