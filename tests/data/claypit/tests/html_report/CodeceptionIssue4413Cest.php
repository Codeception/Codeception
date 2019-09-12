<?php

use Page\DemoPageObject;

class CodeceptionIssue4413Cest {

  public function case1_ORIG(DumbGuy $I)
  {
    $I->comment('no metaStep');
    $I->comment('no metaStep');
  }

  public function case1(DemoPageObject $pageObject)
  {
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->getActor()->comment('no metaStep');
  }

  public function case2(DemoPageObject $pageObject)
  {
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction1();
    $pageObject->getActor()->comment('no metaStep');
  }

  public function case3(DemoPageObject $pageObject)
  {
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction1();
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction2();
  }

  public function case4(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1();
    $pageObject->demoAction2();
  }

  public function case5(DemoPageObject $pageObject)
  {
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction1WithNestedNoMetastep();
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction2();
  }

  public function case5b(DemoPageObject $pageObject)
  {
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction1WithNestedNoMetastep2();
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction2();
  }

  public function case6(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1WithNestedNoMetastep();
    $pageObject->demoAction2();
  }

  public function case6b(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1WithNestedNoMetastep2();
    $pageObject->demoAction2();
  }

  public function case7(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1();
    $pageObject->demoAction1();
  }

  public function case8(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1();
    $pageObject->demoAction1();
    $pageObject->demoAction2();
  }

  public function case9(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1();
    $pageObject->demoAction2();
    $pageObject->demoAction1();
  }

  public function case10(DemoPageObject $pageObject)
  {
    $pageObject->demoAction2();
    $pageObject->demoAction1();
    $pageObject->demoAction1();
  }

}