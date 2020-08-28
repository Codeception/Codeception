<?php
namespace Codeception\Coverage;

use Codeception\Stub;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;

use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;

class FilterTest extends \Codeception\Test\Unit
{
    /**
     * @var Filter
     */
    protected $filter;

    protected function _before()
    {
        $driver = Stub::makeEmpty('SebastianBergmann\CodeCoverage\Driver\Driver');
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
                        'tests/unit/CodeGuy.php'
                    ]
                ]
            ]
        ];
        $this->filter->whiteList($config);
        $fileFilter = $this->filter->getFilter();
        $filterMethod = $this->getFilterMethod();
        $this->assertFalse($fileFilter->$filterMethod(codecept_root_dir('tests/unit/C3Test.php')));
        $this->assertFalse($fileFilter->$filterMethod(codecept_root_dir('src/Codeception/Codecept.php')));
        $this->assertTrue($fileFilter->$filterMethod(codecept_root_dir('vendor/guzzlehttp/guzzle/src/Client.php')));
        $this->assertTrue($fileFilter->$filterMethod(codecept_root_dir('tests/unit/CodeGuy.php')));
    }

    public function testShortcutFilter()
    {
        $config = ['coverage' => [
            'include' => ['tests/*'],
            'exclude' => ['tests/unit/CodeGuy.php']
        ]];
        $this->filter->whiteList($config);
        $fileFilter = $this->filter->getFilter();
        $filterMethod = $this->getFilterMethod();
        $this->assertFalse($fileFilter->$filterMethod(codecept_root_dir('tests/unit/C3Test.php')));
        $this->assertTrue($fileFilter->$filterMethod(codecept_root_dir('tests/unit/CodeGuy.php')));
    }

    /**
     * @return string
     */
    private function getFilterMethod()
    {
        $filterMethod = 'isFiltered';
        if (method_exists($this->filter->getFilter(), 'isExcluded')) {
            //php-code-coverage 9+
            $filterMethod = 'isExcluded';
        }
        return $filterMethod;
    }
}
