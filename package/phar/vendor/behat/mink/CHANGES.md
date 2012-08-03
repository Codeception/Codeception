1.4.0 / 2012-05-40
==================

  * New `Session::selectWindow()` and `Session::selectIFrame()` methods
  * New built-in `WebAssert` class
  * Fixed DocBlocks (autocompletion in any IDE now should just work)
  * Moved Behat-related code into `Behat\MinkExtension`
  * Removed PHPUnit test case class
  * Updated composer dependencies to not require custom repository anymore
  * All drivers moved into separate packages

1.3.3 / 2012-03-23
==================

  * Prevent exceptions in `__toString()`
  * Added couple of useful step definitions for Behat
  * Fixed issues #168, #211, #212, #208
  * Lot of small bug fixes and improvements
  * Fixed dependencies and composer installation routine

1.3.2 / 2011-12-21
==================

  * Fixed webdriver registration in MinkContext

1.3.1 / 2011-12-21
==================

  * Fixed Composer package

1.3.0 / 2011-12-21
==================

  * Brand new Selenium2Driver (webdriver session)
  * Multiselect bugfixes
  * ZombieDriver back in the business
  * Composer now manages dependencies
  * Some MinkContext steps got fixes
  * Lots of bug fixes and cleanup

1.2.0 / 2011-11-04
==================

  * Brand new SeleniumDriver (thanks @alexandresalome)
  * Multiselect support (multiple options selection), including new Behat steps
  * Ability to select option by it's text (in addition to value)
  * ZombieDriver updates
  * Use SuiteHooks to populate parameters (no need to call parent __construct anymore)
  * Updated Goutte and all vendors
  * Lot of bugfixes and new tests

1.1.1 / 2011-08-12
==================

  * Fixed Zombie.js server termination on Linux
  * Fixed base_url usage for external URLs

1.1.0 / 2011-08-08
==================

  * Added Zombie.js driver (thanks @b00giZm)
  * Added pt translation (thanks Daniel Gomes)
  * Refactored MinkContext and MinkTestCase

1.0.3 / 2011-08-02
==================

  * File uploads for empty fields fixed (GoutteDriver)
  * Lazy sessions restart
  * `show_tmp_dir` option in MinkContext
  * Updated to stable Symfony2 components
  * SahiClient connection limit bumped to 60 seconds
  * Dutch language support

1.0.2 / 2011-07-22
==================

  * ElementHtmlException fixed (thanks @Stof)

1.0.1 / 2011-07-21
==================

  * Fixed buggy assertions in MinkContext

1.0.0 / 2011-07-20
==================

  * Added missing tests for almost everything
  * Hude speedup for SahiDriver
  * Support for Behat 2.0 contexts
  * Bundled PHPUnit TestCase
  * Deep element traversing
  * Correct behavior of getText() method
  * New getHtml() method
  * Basic HTTP auth support
  * Soft and hard session resetting
  * Cookies management
  * Browser history interactions (reload(), back(), forward())
  * Weaverryan'd exception messages
  * Huge amount of bugfixes and small additions

0.3.2 / 2011-06-20
==================

  * Fixed file uploads in Goutte driver
  * Fixed setting of long texts into fields
  * Added getPlainText() (returns text without tags and whitespaces) method to the element's API
  * Start_url is now optional parameter
  * Default session (if needed) name now need to be always specified by hands with setDefaultSessionName()
  * default_driver => default_session
  * Updated Symfony Components

0.3.1 / 2011-05-17
==================

  * Small SahiClient update (it generates SID now if no provided)
  * setActiveSessionName => setDefaultSessionName method rename

0.3.0 / 2011-05-17
==================

  * Rewritten from scratch Mink drivers handler. Now it's sessions handler. And Mink now
    sessions-centric tool. See examples in readme. Much cleaner API now.

0.2.4 / 2011-05-12
==================

  * Fixed wrong url locator function
  * Fixed wrong regex in `should see` step
  * Fixed delimiters use in `should see` step
  * Added url-match step for checking urls against regex

0.2.3 / 2011-05-01
==================

  * Updated SahiClient with new version, which is faster and cleaner with it's exceptions

0.2.2 / 2011-05-01
==================

  * Ability to use already started browser as SahiDriver aim
  * Added japanese translation for bundled steps (thanks @hidenorigoto)
  * 10 seconds limit for browser connection in SahiDriver

0.2.1 / 2011-04-21
==================

  * Fixed some bundled step definitions

0.2.0 / 2011-04-21
==================

  * Additional step definitions
  * Support for extended drivers configuration through behat.yml environment parameters
  * Lots of new named selectors
  * Bug fixes
  * Small improvements

0.1.2 / 2011-04-08
==================

  * Fixed Sahi url escaping

0.1.1 / 2011-04-06
==================

  * Fixed should/should_not steps
  * Added spanish translation
  * Fixed forms to use <base> element
  * Fixed small UnsupportedByDriverException issue

0.1.0 / 2011-04-04
==================

  * Initial release
