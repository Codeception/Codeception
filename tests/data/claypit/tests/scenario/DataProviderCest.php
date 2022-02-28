<?php

use Codeception\Example;

/**
* @group dataprovider
*/
class DataProviderCest
{
    /**
     * @group dataprovider
     * @dataProvider __exampleDataSource
     */
    public function withDataProvider(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    /**
     * @group dataprovider
     * @dataprovider protectedDataSource
     */
    public function withProtectedDataProvider(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    /**
     * @group dataprovider
     * @dataProvider __exampleDataSource
     * @example(path=".", file="skipped.suite.yml")
     */
    public function withDataProviderAndExample(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    /**
     * @group dataprovider
     * @depends Codeception\Demo\Depends\DependencyForCest:forTestPurpose
     * @dataprovider protectedDataSource
     */
    public function testDependsWithDataProvider(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    /**
     * @group dataprovider
     * @depends DataProviderCest:testDependsWithDataProvider
     */
    public function testDependsOnTestWithDataProvider(): bool
    {
        return true;
    }

    /** @dataProvider __exampleDataSource */
    public function singleLineAnnotationDataProvider(ScenarioGuy $I, Example $example)
    {
        $I->amInPath($example['path']);
        $I->seeFileFound($example['file']);
    }

    /**
     * @return array
     */
    public function __exampleDataSource(): array
    {
        return[
              ['path' => ".", 'file' => "scenario.suite.yml"],
              ['path' => ".",  'file' => "dummy.suite.yml"],
              ['path' => ".",  'file' => "unit.suite.yml"]
          ];
    }

    /**
     * @return array
     */
    protected function protectedDataSource(): array
    {
        return[
              ['path' => ".", 'file' => "scenario.suite.yml"],
              ['path' => ".",  'file' => "dummy.suite.yml"],
              ['path' => ".",  'file' => "unit.suite.yml"]
          ];
    }
}
