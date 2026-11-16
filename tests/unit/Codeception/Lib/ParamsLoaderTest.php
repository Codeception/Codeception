<?php

declare(strict_types=1);

namespace Codeception\Lib;

use Codeception\Attribute\Group;
use Codeception\Codecept;

class ParamsLoaderTest extends \Codeception\Test\Unit
{
    #[Group('core')]
    public function testPhpConstantsInYamlFile()
    {
        $params = ParamsLoader::load(codecept_data_dir('params/params_constants.yml'));

        $this->assertSame(PHP_INT_MAX, $params['global']);
        $this->assertSame(Codecept::VERSION, $params['class']);
    }
}
