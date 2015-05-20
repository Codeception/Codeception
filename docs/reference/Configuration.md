# Configuration

Default your global configuration file will be this:

```yaml
# can be changed while bootstrapping project
actor: Tester 

paths:
    # where the modules stored
    tests: tests

    # directory for fixture data    
    data: tests/_data

    # directory for custom modules (helpers)
    helpers: tests/_support

settings:

    # name of bootstrap that will be used
    # each bootstrap file should be 
    # inside a suite directory.

    bootstrap: _bootstrap.php

    # You can extend the suite class if you need to.
    suite_class: \PHPUnit_Framework_TestSuite

    # by default it's false on Windows
    # use [ANSICON](http://adoxa.110mb.com/ansicon/) to colorize output.
    colors: true

    # Tests (especially functional) can take a lot of memory
    # We set a high limit for them by default.
    memory_limit: 1024M


# Global modules configuration.    
modules:
    config:
        Db:
            dsn: ''
            user: ''
            password: ''
            dump: tests/_data/dump.sql
```

Suite configuration acceptance.yml

```yaml
class_name: AcceptanceTester
modules:
    # enabled modules and helpers
    enabled:
        - PhpBrowser
        - AcceptanceHelper
        - Db

    # local module configuration. Overrides the global.        
    config:
        Db:
            dsn:
```
