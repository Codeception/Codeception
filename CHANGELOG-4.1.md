#### 4.1.12

* Dependency Injection: Fix PHP types being treated as classes #6031 by @cs278

#### 4.1.11

* Another patch for class constant default values #6027 by @mwi-gofore

#### 4.1.10

* Use fully qualified name for class constant defaults #6016 by @lastzero
* add ServerConstAdapter for phpdotenv v5 #6015 by #retnek

#### 4.1.9

* Support PHP 8 #5999
* Generate correct default values in Actions files #5999
* Use sendGet in Api template #5998 by @ThomasLandauer

#### 4.1.8

* Support Covertura code coverage format #5994 by @zachkknowbe4
* Compatibility with vlucas/phpdotenv v5 #5975 by @johanzandstra
* Support absolute output dir path on Windows #5966 by @Naktibalda
* Fix --no-redirect option for run command #5967 by @convenient
* Code coverage: Don't make request to c3.php if remote=false #5991 by @dereuromark
* Gherkin: Fail on ambiguous step definition #5866 by @matthiasnoback
* Removed complicated merge logic for environment configurations #5948 by @Sasti
* Logger extension: add .log to suite log files #5982 by @varp

#### 4.1.7

* Compatibility with PhpCodeCoverage 9 and PHPUnit 9.3
* Show snapshot diff on fail #5930 by @fkupper
* Ability to store non-json snapshots #5945 by @fkupperr
* Fixed step decorators in generated configuration file #5936 by @rene-hermenau
* Fixed single line style dataprovider #5944 by @edno

#### 4.1.6

* Compatibility with PHPUnit 9.2

#### 4.1.5

* Fixed docker images
* Fix indentation in generated Actor class, by @cebe
* Added addToAssertionCount method to AssertionCounter trait, #5918 by @Archanium

#### 4.1.4

* Build: Fix bug with void type not being picked up correctly #5880 by @Jamesking56
* Test --report flag (the bugfix in phpunit-wrapper library)

#### 4.1.3

* Build: Use non-deprecated method to get return type hint on PHP 7.1+ #5876
* Build: Ensure that the return keyword is not used when method returns void type #5878 by @Jamesking56

#### 4.1.2

* Fixed --no-redirect option does not exist error #5857 by @liamjtoohey
* Init command: Check the composer option config.vendor_dir when updating composer #5871 by @gabriel-lima96
* Build: Add return type hint to the generated actions above PHP 7.0 #5862 by @pezia
* Prevent merged config array ballooning in memory #5871 by @AndrewFeeney
* Do not truncate arguments for --html options #5870 by @adaniloff

#### 4.1.1

* --no-artifacts flag for run command #5646 by @Mitrichius
* Fix recorder filename with special chars #5846 by @gimler

#### 4.1.0

* Support for PHPUnit 9
