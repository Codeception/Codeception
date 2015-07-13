<?php
namespace Codeception\Coverage;

class FilterTest extends \Codeception\TestCase\Test
{
    /**
     * @var Filter
     */
    protected $filter;

    protected function _before()
    {
        $this->filter = new Filter(new DummyCodeCoverage());
    }

    // tests
    public function testBacklistFiltersApplied()
    {
        $config = ['coverage' =>
            ['blacklist' => [
                'include' => [
                    'tests/*',
                    'vendor/*/*Test.php',
                    'src/Codeception/Codecept.php'
                ],
                'exclude' => [
                    'tests/support/CodeGuy.php'
                ]
            ]
        ]];
        $this->filter->blackList($config);
        $fileFilter = $this->filter->getFilter();
        $this->assertTrue($fileFilter->isFiltered(codecept_root_dir('tests/unit/c3Test.php')));
        $this->assertTrue($fileFilter->isFiltered(codecept_root_dir('src/Codeception/Codecept.php')));
        $this->assertTrue($fileFilter->isFiltered(codecept_root_dir('vendor/phpunit/phpunit/tests/Framework/AssertTest.php')));
        $this->assertFalse($fileFilter->isFiltered(codecept_root_dir('vendor/guzzlehttp/guzzle/src/Client.php')));
        $this->assertFalse($fileFilter->isFiltered(codecept_root_dir('tests/support/CodeGuy.php')));
    }

    public function testWhitelistFilterApplied()
    {
        $config = ['coverage' =>
            ['whitelist' => [
                'include' => [
                    'tests/*',
                    'vendor/*/*Test.php',
                    'src/Codeception/Codecept.php'
                ],
                'exclude' => [
                    'tests/unit/CodeGuy.php'
                ]
            ]
        ]];
        $this->filter->whiteList($config);
        $fileFilter = $this->filter->getFilter();
        $this->assertFalse($fileFilter->isFiltered(codecept_root_dir('tests/unit/c3Test.php')));
        $this->assertFalse($fileFilter->isFiltered(codecept_root_dir('src/Codeception/Codecept.php')));
        $this->assertFalse($fileFilter->isFiltered(codecept_root_dir('vendor/phpunit/phpunit/tests/Framework/AssertTest.php')));
        $this->assertTrue($fileFilter->isFiltered(codecept_root_dir('vendor/guzzlehttp/guzzle/src/Client.php')));
        $this->assertTrue($fileFilter->isFiltered(codecept_root_dir('tests/unit/CodeGuy.php')));
    }

    public function testShortcutFilter()
    {
        $config = ['coverage' => [
            'include' => ['tests/*'],
            'exclude' => ['tests/unit/CodeGuy.php']
        ]];
        $this->filter->whiteList($config);
        $fileFilter = $this->filter->getFilter();
        $this->assertFalse($fileFilter->isFiltered(codecept_root_dir('tests/unit/c3Test.php')));
        $this->assertTrue($fileFilter->isFiltered(codecept_root_dir('tests/unit/CodeGuy.php')));

    }

}