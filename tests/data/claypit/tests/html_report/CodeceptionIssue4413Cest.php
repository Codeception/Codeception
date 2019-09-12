<?php

use Page\DemoPageObject;

/**
 * Note: The references to different test cases like "case 1" are with respect to
 * https://github.com/Codeception/Codeception/issues/3410 which is the Codeception issue that lead to fixes of the HTML
 * report in 2016 (!): except that the test cases of #3410 were not complete and missed the bugs fixed in the course of
 * #4413 here...
 */
class CodeceptionIssue4413Cest {

  // #3410: original case 1
  public function twoCommentStepsInARow(DumbGuy $I)
  {
    $I->comment('no metaStep');
    $I->comment('no metaStep');
  }

  // #3410: (slightly adaption of) original case 1
  public function twoCommentStepsInARowViaPageObjectActor(DemoPageObject $pageObject)
  {
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->getActor()->comment('no metaStep');
  }

  // #3410: like original case 2
  public function twoCommentStepsWithOneSubStepInBetween(DemoPageObject $pageObject)
  {
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction1();
    $pageObject->getActor()->comment('no metaStep');
  }

  // #3410: like original case 3
  public function commentStepsWithDifferentSubStepsInBetweenAndAfter(DemoPageObject $pageObject)
  {
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction1();
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction2();
  }

  // #3410: like original case 4
  public function differentSubSteps(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1();
    $pageObject->demoAction2();
  }

  // #3410: like original case 5
  public function commentStepsWithDifferentSubStepsOnceNestedInBetweenAndAfter(DemoPageObject $pageObject)
  {
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction1WithNestedNoMetastep();
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction2();
  }

  // #3410: (slightly adaption of) original case 5
  public function commentStepsWithDifferentSubStepsOnceNestedInBetweenAndAfter2(DemoPageObject $pageObject)
  {
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction1WithNestedNoMetastep2();
    $pageObject->getActor()->comment('no metaStep');
    $pageObject->demoAction2();
  }

  // #3410: like original case 6
  public function nestedSubStepFollowedByOtherSubStep(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1WithNestedNoMetastep();
    $pageObject->demoAction2();
  }

  // #3410: (slightly adaption of) original case 6
  public function nestedSubStepFollowedByOtherSubStep2(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1WithNestedNoMetastep2();
    $pageObject->demoAction2();
  }

  // #4413: new case 7
  public function twoIdentialSubStepsInARow(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1();
    $pageObject->demoAction1();
  }

  // #4413: new case 8
  public function twoIdentialSubStepsInARowFollowedByAnotherSubStep(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1();
    $pageObject->demoAction1();
    $pageObject->demoAction2();
  }

  // #4413: new case 9
  public function twoIdentialSubStepsWithAnotherSubStepInBetween(DemoPageObject $pageObject)
  {
    $pageObject->demoAction1();
    $pageObject->demoAction2();
    $pageObject->demoAction1();
  }

  // #4413: new case 10
  public function subStepFollowedByTwoIdentialSubSteps(DemoPageObject $pageObject)
  {
    $pageObject->demoAction2();
    $pageObject->demoAction1();
    $pageObject->demoAction1();
  }

}