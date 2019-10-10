<?php

class CodeceptionIssue5568Cest {

  public function failureShouldCreateHtmlSnapshot(AcceptanceTester $I) {
    $I->amOnPage('/');
    $I->see('SomethingThatIsNotThereToFailTheTest');
  }

}