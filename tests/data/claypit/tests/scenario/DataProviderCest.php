<?php
use Codeception\Example;

class DataProviderCest
{
     /**
      * @dataprovider __exampleDataSource
      */
      public function withDataProvider(ScenarioGuy $I, Example $example)
      {
          $I->amInPath($example['path']);
          $I->seeFileFound($example['file']);
      }

      /**
       * @dataprovider protectedDataSource
       */
       public function withProtectedDataProvider(ScenarioGuy $I, Example $example)
       {
           $I->amInPath($example['path']);
           $I->seeFileFound($example['file']);
       }

      /**
       * @dataprovider __exampleDataSource
       * @example(path=".", file="skipped.suite.yml")
       */
       public function withDataProviderAndExample(ScenarioGuy $I, Example $example)
       {
           $I->amInPath($example['path']);
           $I->seeFileFound($example['file']);
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
