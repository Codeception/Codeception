<?php
use Codeception\Example;

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
       public function testDependsOnTestWithDataProvider()
       {
           return true;
       }

      /**
       * @return array
       */
      public function __exampleDataSource()
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
      protected function protectedDataSource()
      {
          return[
              ['path' => ".", 'file' => "scenario.suite.yml"],
              ['path' => ".",  'file' => "dummy.suite.yml"],
              ['path' => ".",  'file' => "unit.suite.yml"]
          ];
      }
}
