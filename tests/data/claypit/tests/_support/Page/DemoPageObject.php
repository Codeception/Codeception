<?php

namespace Page;

class DemoPageObject {

  /**
   * @var \DumbGuy
   */
  private $I;

  public function __construct(\DumbGuy $I) {
    $this->I = $I;
  }

  /**
   * @return \DumbGuy
   */
  public function getActor() {
    return $this->I;
  }

  public function demoAction1() {
    $this->I->dontSeeFileFound('thisFileDoesNotExist');
    $this->I->dontSeeFileFound('thisFileAlsoDoesNotExist');
    return $this;
  }

  public function demoAction2() {
    $this->I->dontSeeFileFound('thisFileAgainDoesNotExist');
    return $this;
  }

  public function demoAction1WithNestedNoMetastep() {
    $this->demoAction1();
    $this->I->comment('no metaStep inside a method');
    return $this;
  }

  public function demoAction1WithNestedNoMetastep2() {
    $this->demoAction1();
    $this->internalNoMetastep();
    return $this;
  }
  private function internalNoMetastep() {
    $this->I->comment('no metaStep inside a private internal method');
    return $this;
  }
}