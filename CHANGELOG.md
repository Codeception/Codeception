# Changelog

#### 2.0.0-RC 04/02/2014

* [WebDriver] makeScreenshot does not use filename of a test


#### 2.0.0-RC 03/21/2014

* added grabAttributeFrom
* seeElement to accept attributes in second parameter: seeElement('input',['name'=>'login'])


#### 2.0.0-beta 03/19/2014

* executeInGuzzle is back in PhpBrowser
* environment can be accessed via ->env in test


#### 2.0.0-beta 03/18/2014

* before/after methods of Cest can take  object
* moved logger to extension
* bootstrap files are loaded before suite only
* extension can reconfigure global config


#### 2.0.0-beta 03/17/2014

* removed RefactorAddNamespace and Analyze commands
* added options to set output files for xml, html reports, and coverage
* added extension to rerun failed tests
* webdriver upgraded to 0.4
* upgraded to PHPUnit 4