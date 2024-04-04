<?php

declare(strict_types=1);

namespace Codeception\Coverage;

use Codeception\Stub;
use PHPUnit\Runner\Version as VersionAlias;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;

class FilterTest extends \Codeception\Test\Unit
{
    protected \CodeGuy $tester;

    protected Filter $filter;

    protected function _before()
    {
        $driver = Stub::makeEmpty(\SebastianBergmann\CodeCoverage\Driver\Driver::class);
        $this->filter = new Filter(new CodeCoverage($driver, new CodeCoverageFilter()));
    }

    public function testWhitelistFilterApplied()
    {
        $config = [
            'coverage' => [
                'whitelist' => [
                    'include' => [
                        'tests/*',
                        'vendor/*/*Test.php',
                        'src/Codeception/Codecept.php'
                    ],
                    'exclude' => [
                        'tests/support/CodeGuy.php'
                    ]
                ]
            ]
        ];
        $this->filter->whiteList($config);
        $fileFilter = $this->filter->getFilter();
        $this->assertFalse($fileFilter->isExcluded(codecept_root_dir('tests/unit/C3Test.php')));
        $this->assertFalse($fileFilter->isExcluded(codecept_root_dir('src/Codeception/Codecept.php')));
        $this->assertTrue($fileFilter->isExcluded(codecept_root_dir('vendor/guzzlehttp/guzzle/src/Client.php')));
        $this->assertTrue($fileFilter->isExcluded(codecept_root_dir('tests/support/CodeGuy.php')));
        $this->assertTrue(
            $fileFilter->isExcluded(
                codecept_root_dir('tests/unit.suite.yml')
            ),
            'tests/unit.suite.yml appears in file list'
        );
    }

    public function testShortcutFilter()
    {
        $config = ['coverage' => [
            'include' => ['tests/*'],
            'exclude' => ['tests/support/CodeGuy.php']
        ]];
        $this->filter->whiteList($config);
        $fileFilter = $this->filter->getFilter();
        $this->assertFalse($fileFilter->isExcluded(codecept_root_dir('tests/unit/C3Test.php')));
        $this->assertTrue($fileFilter->isExcluded(codecept_root_dir('tests/support/CodeGuy.php')));
    }

    public function testWhitelistIncludeFilterApplied()
    {
        $config = [
            'coverage' => [
                'whitelist' => [
                    'include' => [
                        'tests/*',
                        'vendor/*/*Test.php',
                        'src/Codeception/Codecept.php'
                    ],
                ]
            ]
        ];
        $this->filter->whiteList($config);
        $fileFilter = $this->filter->getFilter();
        $this->assertFalse($fileFilter->isExcluded(codecept_root_dir('tests/unit/C3Test.php')));
        $this->assertFalse($fileFilter->isExcluded(codecept_root_dir('src/Codeception/Codecept.php')));
        $this->assertFalse($fileFilter->isExcluded(codecept_root_dir('tests/support/CodeGuy.php')));
        $this->assertTrue($fileFilter->isExcluded(codecept_root_dir('vendor/guzzlehttp/guzzle/src/Client.php')));
        $this->assertTrue(
            $fileFilter->isExcluded(
                codecept_root_dir('tests/unit.suite.yml')
            ),
            'tests/unit.suite.yml appears in file list'
        );
    }
}
