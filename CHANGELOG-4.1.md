#### 4.1.8

* Add compatibility with vlucas/phpdotenv v5

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
