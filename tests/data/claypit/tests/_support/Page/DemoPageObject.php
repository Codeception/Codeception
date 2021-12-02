<?php

namespace Page;

class DemoPageObject {

  /**
   * @var \DumbGuy
   */
  private \DumbGuy $I;

  public function __construct(\DumbGuy $I) {
    $this->I = $I;
  }

  /**
   * @return \DumbGuy
   */
  public function getActor(): \DumbGuy
  {
    return $this->I;
  }

  public function demoAction1(): DemoPageObject
  {
    $this->I->dontSeeFileFound('thisFileDoesNotExist');
    $this->I->dontSeeFileFound('thisFileAlsoDoesNotExist');
    return $this;
  }

  public function demoAction2(): DemoPageObject
  {
    $this->I->dontSeeFileFound('thisFileAgainDoesNotExist');
    return $this;
  }

  public function demoAction1WithNestedNoMetastep(): DemoPageObject
  {
    $this->demoAction1();
    $this->I->comment('no metaStep inside a method');
    return $this;
  }

  public function demoAction1WithNestedNoMetastep2(): DemoPageObject
  {
    $this->demoAction1();
    $this->internalNoMetastep();
    return $this;
  }
  private function internalNoMetastep(): DemoPageObject
  {
    $this->I->comment('no metaStep inside a private internal method');
    return $this;
  }
}
