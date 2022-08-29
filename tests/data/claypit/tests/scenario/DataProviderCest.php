<?php

use Codeception\Attribute\DataProvider;
use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Codeception\Demo\Depends\DependencyForCest;
use Codeception\Example;

#[Group('dataprovider')]
class DataProviderCest
{
    #[Group('dataprovider')]
    #[DataProvider('__exampleDataSource')]
    public function withDataProvider(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    #[Group('dataprovider')]
    #[DataProvider('protectedDataSource')]
    public function withProtectedDataProvider(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    /**
     * @example(path=".", file="skipped.suite.yml")
     */
    #[Group('dataprovider')]
    #[DataProvider('__exampleDataSource')]
    public function withDataProviderAndExample(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    #[Group('dataprovider')]
    #[Depends(DependencyForCest::class . ':forTestPurpose')]
    #[DataProvider('protectedDataSource')]
    public function testDependsWithDataProvider(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    #[Group('dataprovider')]
    #[Depends(DataProviderCest::class . ':testDependsWithDataProvider')]
    public function testDependsOnTestWithDataProvider(): bool
    {
        return true;
    }

    #[Group('dataprovider')]
    #[DataProvider('__exampleDataSource')]
    public function singleLineAnnotationDataProvider(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    public function __exampleDataSource(): array
    {
        return[
              ['path' => ".", 'file' => "scenario.suite.yml"],
              ['path' => ".",  'file' => "dummy.suite.yml"],
              ['path' => ".",  'file' => "unit.suite.yml"]
          ];
    }

    protected function protectedDataSource(): array
    {
        return[
              ['path' => ".", 'file' => "scenario.suite.yml"],
              ['path' => ".",  'file' => "dummy.suite.yml"],
              ['path' => ".",  'file' => "unit.suite.yml"]
          ];
    }
}
